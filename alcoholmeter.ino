#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>

// กำหนดค่าต่างๆ
const char* ssid = "IRPC-Trainning";
const char* password = "";
const char* server_url = "https://10.29.196.223/sensor_db/fetch.php";
const char* line_notify_token = "9Aas8AC9iHW8doL8pOyLiAm4NzgeCrfWQDPhI9yJU8l";

#define SS_PIN 5
#define RST_PIN 4
#define BUZZER_PIN 16
#define SWITCH_PIN 17
#define ANALOG_PIN 15

#define BUZZER_TONE_FREQUENCY 100

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1

// ช่วงเวลาขั้นต่ำในการส่งข้อมูล (มิลลิวินาที)
const unsigned long dataSendInterval = 5000;
//  ช่วงเวลาในการอ่านบัตร (มิลลิวินาที)
unsigned long cardReadInterval = 1000;

// คลาสสำหรับจัดการ RFID
class RFIDReader {
  public:
    RFIDReader(int ssPin, int rstPin) : mfrc522(ssPin, rstPin) {}

    void initialize() {
      SPI.begin();
      mfrc522.PCD_Init();
    }

    String readUID() {
      String UID_Result = "";
      if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
        char str[32];
        byteArray_to_string(mfrc522.uid.uidByte, mfrc522.uid.size, str);
        UID_Result = str;
        // mfrc522.PICC_HaltA();
        // mfrc522.PCD_StopCrypto1();
      }
      return UID_Result;
    }

  private:
    MFRC522 mfrc522;

    void byteArray_to_string(byte array[], unsigned int len, char buffer[]) {
      for (unsigned int i = 0; i < len; i++) {
        byte nib1 = (array[i] >> 4) & 0x0F;
        byte nib2 = (array[i] >> 0) & 0x0F;
        buffer[i * 2 + 0] = nib1 < 0xA ? '0' + nib1 : 'A' + nib1 - 0xA;
        buffer[i * 2 + 1] = nib2 < 0xA ? '0' + nib2 : 'A' + nib2 - 0xA;
      }
      buffer[len * 2] = '\0';
    }
};

// คลาสสำหรับจัดการ OLED
class OLEDDisplay {
  public:
    OLEDDisplay() : display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET) {}

    void initialize() {
      if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println(F("SSD1306 allocation failed"));
        for (;;);
      }
      display.display();
      delay(100);
      display.clearDisplay();
    }

    void displayMessage(const String& message, int size = 1) {
      display.clearDisplay();
      display.setTextSize(size);
      display.setTextColor(SSD1306_WHITE);
      display.setCursor(0, 0);
      display.println(message);
      display.display();
    }

  private:
    Adafruit_SSD1306 display;
};

// คลาสสำหรับการเชื่อมต่อ WiFi
class WiFiConnector {
  public:
    WiFiConnector(const char* ssid, const char* password)
      : _ssid(ssid), _password(password) {}

    bool connect() {
      WiFi.begin(_ssid, _password);

      unsigned long startTime = millis();
      while (WiFi.status() != WL_CONNECTED && (millis() - startTime) < 10000) {
        delay(100);
      }

      return WiFi.status() == WL_CONNECTED;
    }

    void disconnect() {
      WiFi.disconnect();
    }

  private:
    const char* _ssid;
    const char* _password;
};

// คลาสสำหรับจัดการ HTTP request
class HTTPRequestManager {
public:
  String getResponse(const String& url) {
    HTTPClient http;
    http.begin(url);
    int httpResponseCode = http.GET();
    String response = "";

    if (httpResponseCode > 0) {
      response = http.getString();
    }

    http.end();
    return response;
  }

  int sendData(const String& url) {
    HTTPClient http;
    http.begin(url);
    int httpResponseCode = http.GET();

    Serial.print("URL: ");
    Serial.println(url);

    if (httpResponseCode > 0) {
      Serial.print("Response Code: ");
      Serial.println(httpResponseCode);
      // อ่านและเก็บการตอบกลับจากเซิร์ฟเวอร์
      String payload = http.getString();
      Serial.println("Response: " + payload);
    } else {
      Serial.print("Error sending data. Error code: ");
      Serial.println(httpResponseCode);
    }

    http.end();
    return httpResponseCode;
  }
};

class AlcoholSensor {
  public:
    AlcoholSensor(int analogPin) : _analogPin(analogPin) {}

    float measureAlcohol() {
      // ปิด WiFi ชั่วคราว
      esp_log_level_set("*", ESP_LOG_ERROR);
      WiFi.mode(WIFI_OFF);
      delay(100);  // รอให้ WiFi ปิด

      int numReadings = 100;
      float totalVoltage = 0.0;
      for (int i = 0; i < numReadings; i++) {
        int sensorValue = analogRead(_analogPin);
        float voltage = sensorValue * (3.3 / 4095);
        totalVoltage += voltage;
        delay(100);
      }

      float averageVoltage = totalVoltage / numReadings;
      float RLOAD = 10.0;
      float Rs = (5.0 - averageVoltage) / averageVoltage * RLOAD;
      float Ro = 10.0;
      float ratio = Rs / Ro;
      float ppm = pow(ratio, -1.428) * 10.0;
      float mg_per = ppm;

      Serial.print("PPM: ");
      Serial.print(ppm*4, 4);
      Serial.print(" ALCOHOL: ");
      Serial.print(mg_per*4, 4);
      Serial.println(" MG% ");

      return mg_per*4;
    }

  private:
    int _analogPin;
};
// คลาสสำหรับจัดการ Line Notify
class LineNotifier {
  public:
    LineNotifier(const char* token) : _token(token) {}

    void sendMessage(const String& message) {
      if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        http.begin("https://notify-api.line.me/api/notify");
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        http.addHeader("Authorization", "Bearer " + String(_token));
        String postData = "message=" + urlencode(message);
        int httpResponseCode = http.POST(postData);

        if (httpResponseCode > 0) {
          String response = http.getString();
          Serial.println("LINE Notify Response: " + response);
        } else {
          Serial.println("Error sending LINE Notify: " + String(httpResponseCode));
        }

        http.end();

      } else {
        Serial.println("WiFi not connected");
      }
    }
  private:
    const char* _token;

    // ฟังก์ชัน URL encoding
    String urlencode(String str) {
      String encodedString = "";
      char c;
      char code0;
      char code1;
      char code2;
      for (int i = 0; i < str.length(); i++) {
        c = str.charAt(i);
        if (c == ' ') {
          encodedString += '+';
        } else if (isalnum(c)) {
          encodedString += c;
        } else {
          code1 = (c & 0xf) + '0';
          if ((c & 0xf) > 9) {
            code1 = (c & 0xf) - 10 + 'A';
          }
          c = (c >> 4) & 0xf;
          code0 = c + '0';
          if (c > 9) {
            code0 = c - 10 + 'A';
          }
          code2 = '\0';
          encodedString += '%';
          encodedString += code0;
          encodedString += code1;
        }
        yield();  // ให้โอกาสการประมวลผลอื่นๆ
      }
      return encodedString;
    }
};

// คลาสหลักสำหรับระบบ
class AlcoholDetectionSystem {
  public:
    AlcoholDetectionSystem(RFIDReader& rfidReader,
                           OLEDDisplay& oledDisplay,
                           WiFiConnector& wifiConnector,
                           HTTPRequestManager& httpRequestManager,
                           AlcoholSensor& alcoholSensor,
                           LineNotifier& lineNotifier)
      : _rfidReader(rfidReader),
        _oledDisplay(oledDisplay),
        _wifiConnector(wifiConnector),
        _httpRequestManager(httpRequestManager),
        _alcoholSensor(alcoholSensor),
        _lineNotifier(lineNotifier) {}

    void setup() {
      Serial.begin(115200);

      _oledDisplay.initialize();

      _rfidReader.initialize();

      pinMode(SWITCH_PIN, INPUT_PULLUP);
      _oledDisplay.displayMessage("");

      _oledDisplay.displayMessage("\n\n\n\n  Connecting WiFi..");

      if (_wifiConnector.connect()) {
        Serial.println("เชื่อมต่อกับ WiFi แล้ว!");
        _oledDisplay.displayMessage("\n\n\n\n  Connected to WiFi!");
        delay(2000);
        _wifiConnected = true;
      } else {
        Serial.println("WiFi Connection Failed");
        _oledDisplay.displayMessage("\n\n\n\n  Connection Failed");
        delay(5000);
        _wifiConnected = false;
      }

      _oledDisplay.displayMessage("\n      PLEASE TAP\n\n      YOUR CARD \n\n         AND \n\n     CLICK BOTTON");
    }

    void loop() {
      // อ่านบัตร RFID ทุกๆ cardReadInterval มิลลิวินาที
      if (millis() - _lastCardReadTime >= cardReadInterval) {
        _lastCardReadTime = millis();
        _rfidUid = _rfidReader.readUID();
        if (_rfidUid != "") {
          Serial.println();
          Serial.print("UID : ");
          Serial.println(_rfidUid);

          if (!_wifiConnected) {
            
            _oledDisplay.displayMessage("\n      PLEASE TAP\n\n      YOUR CARD \n\n         AND \n\n     CLICK BUTTON");
            delay(2000);
            

          }
        }
      }

      // ตรวจสอบการกดปุ่มโดยไม่ต้องเชื่อมต่อ WiFi
      if (millis() - _lastButtonPressTime > _buttonDebounceDelay) {
        if (digitalRead(SWITCH_PIN) == LOW) {
          _lastButtonPressTime = millis();
          handleSwitchPressed();
        }
      }

      // ส่งข้อมูลเมื่อมีการเชื่อมต่อ WiFi, มีข้อมูล RFID, 
      // ยังไม่ได้ส่งข้อมูล และเลยเวลา dataSendInterval แล้ว
      if (_wifiConnected &&
          !_isDataSent &&
          (millis() - _lastDataSentTime >= dataSendInterval) &&
          _rfidUid != "") {
        handleRFIDDetected();
      } else {
        // รีเซ็ตสถานะการส่งข้อมูล
        _isDataSent = false;

        // แสดงข้อความเริ่มต้น
        if (!_wifiConnected) {
           _oledDisplay.displayMessage("\n      PLEASE TAP\n\n      YOUR CARD \n\n         AND \n\n     CLICK BUTTON");
        
        }
      }

      delay(200);
    }

  private:
    RFIDReader& _rfidReader;
    OLEDDisplay& _oledDisplay;
    WiFiConnector& _wifiConnector;
    HTTPRequestManager& _httpRequestManager;
    AlcoholSensor& _alcoholSensor;
    LineNotifier& _lineNotifier;

    bool _wifiConnected = false;
    String _rfidUid = "";
    bool _isDataSent = false;
    unsigned long _lastDataSentTime = 0;
    unsigned long _lastCardReadTime = 0;
    unsigned long _lastButtonPressTime = 0;
    const unsigned long _buttonDebounceDelay = 200;

    void handleSwitchPressed() {
      Serial.println("Switch ON");

      _oledDisplay.displayMessage("\n\n     PLEASE WAIT \n\n        10 SEC\n\n  TO MEASURE ALCOHOL");

      float alcoholValue = _alcoholSensor.measureAlcohol();
      // แสดงผลบนจอ OLED
      _oledDisplay.displayMessage("\n\n\n  ALCOHOL: " + String(alcoholValue, 4) + " MG%", 1);
      delay(10000);
      _oledDisplay.displayMessage("\n      PLEASE TAP\n\n      YOUR CARD \n\n         AND \n\n     CLICK BOTTON");
    }

    void handleRFIDDetected() {
      delay(500);
      _oledDisplay.displayMessage("");
      _oledDisplay.displayMessage("");
      _oledDisplay.displayMessage("\n\n\n\n Checking information");
      delay(1000);

      if (!checkRFIDExists(_rfidUid)) {
        Serial.println("RFID not found in database!");
        _oledDisplay.displayMessage("");
        _oledDisplay.displayMessage("");
        _oledDisplay.displayMessage("\n\n\n    NOT FOUND DATA\n\n\n PLEASE ADD THIS DATA");
        delay(3000); // แก้ไขเวลาจาก 5000 เป็น 3000
        _rfidUid = ""; // รีเซ็ต RFID UID
        _oledDisplay.displayMessage("\n      PLEASE TAP\n\n      YOUR CARD \n\n         AND \n\n     CLICK BOTTON");
        return; // กลับออกจากฟังก์ชัน
      }
      _oledDisplay.displayMessage("");
      _oledDisplay.displayMessage("\n\n     PLEASE BLOW\n\n     ALCOHOL FOR \n\n     15 SECONDS...");

      _wifiConnector.disconnect();
      delay(100);

      float alcoholValue = _alcoholSensor.measureAlcohol();

      if (alcoholValue > 20) {
        tone(BUZZER_PIN, BUZZER_TONE_FREQUENCY);
      } else {
        noTone(BUZZER_PIN);
      }

      if (_wifiConnector.connect()) {
          Serial.println("Connected to WiFi!");

          String dataToSend = "?rfid=" + _rfidUid + "&alcoholvalue=" + String(alcoholValue, 2);

          Serial.print("Sending data to URL: ");
          Serial.println(server_url);

          // ส่งข้อมูลและรับการตอบกลับ
          String response = _httpRequestManager.getResponse(server_url + dataToSend);

          // ตรวจสอบว่าการตอบกลับไม่ว่างเปล่า
          if (response.length() > 0) {
              StaticJsonDocument<200> doc;
              DeserializationError error = deserializeJson(doc, response);

              if (!error) {
                  String name = doc["name"].as<String>();
                  String identification = doc["identification"].as<String>();

                  if (name.length() > 0 && identification.length() > 0) {
                      _oledDisplay.displayMessage("        ALCOHOL:             " + String(alcoholValue, 2) + " MG%\n\n         NAME:          " + name +
                                              "\n\n    IDENTIFICATION:        " + identification, 1);
                      String message = "\nName: " + name + "\nAlcohol:  " + String(alcoholValue, 2) + " %\nID:  " + identification;
                      _lineNotifier.sendMessage(message);

                      // รอ 3 วินาที
                      delay(12000); 

                      _oledDisplay.displayMessage("\n      PLEASE TAP\n\n      YOUR CARD \n\n         AND \n\n     CLICK BOTTON");

                      // รีเซ็ต RFID UID เพื่อรอการสแกนครั้งใหม่
                      _rfidUid = ""; 
                  } else {
                      Serial.println("Error: Name or Identification is empty");
                  }
              } else {
                  Serial.println("Error parsing JSON response:");
                  Serial.println(error.c_str());
              }
              _isDataSent = true;
              _lastDataSentTime = millis();
          } else {
              Serial.println("Error: Empty response received");
          }
      } else {
          Serial.println("Failed to connect to WiFi");
      }

        noTone(BUZZER_PIN);
        delay(3000);
      }
    

     bool checkRFIDExists(const String& rfid) {
      return rfidExists(rfid, server_url);
    }

     bool rfidExists(const String& rfid, const String& serverUrl) {
      Serial.print("Checking RFID at ");
      Serial.print(serverUrl);
      Serial.print(": ");
      Serial.println(rfid);

      String url = String(serverUrl) + "?check_exists=true&rfid=" + rfid;
      String response = _httpRequestManager.getResponse(url);

      Serial.println("Server Response: " + response);

      if (response.indexOf("true") != -1) {
          return true;
      } else if (response.indexOf("false") != -1) {
          return false;
      } else {
          Serial.println("Unexpected response from server");
      }
      return false;
  }
};

RFIDReader rfidReader(SS_PIN, RST_PIN);
OLEDDisplay oledDisplay;
WiFiConnector wifiConnector(ssid, password);
HTTPRequestManager httpRequestManager;
AlcoholSensor alcoholSensor(ANALOG_PIN);
LineNotifier lineNotifier(line_notify_token);
AlcoholDetectionSystem alcoholDetectionSystem(rfidReader, oledDisplay, wifiConnector, httpRequestManager, alcoholSensor, lineNotifier);

void setup() {
  alcoholDetectionSystem.setup();
}

void loop() {
  alcoholDetectionSystem.loop();
} 