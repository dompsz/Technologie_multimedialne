#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>

// --- KONFIGURACJA SIECI (DHCP) ---
byte mac_addr[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xEC };

// --- KONFIGURACJA BAZY ---
IPAddress server_addr(45, 9, 180, 11);  
char user[] = "pszczolk_z12b";              
char password[] = "g8FHkthKzZ93hrc6HYGp";        

// Zapytania SQL
char INSERT_HELLO[] = "INSERT INTO pszczolk_z12b.hello_arduino (message) VALUES ('Hello from Arduino via Router!')";
// Przykład dla vmeter (v0-v5)
char INSERT_VMETER[] = "INSERT INTO pszczolk_z12b.vmeter (v0, v1, v2, v3, v4, v5) VALUES (%d, %d, %d, %d, %d, %d)";
char query[128];

EthernetClient client;
MySQL_Connection conn((Client *)&client);

void setup() {
  Serial.begin(115200);
  while (!Serial); 
  
  Serial.println("--- TRYB DHCP (Router) ---");
  Serial.println("Pobieranie adresu IP...");
  
  if (Ethernet.begin(mac_addr) == 0) {
    Serial.println("BŁĄD: Nie można pobrać IP z routera! Sprawdź kabel lub port.");
    while (true); 
  }
  
  Serial.print("Moje IP: ");
  Serial.println(Ethernet.localIP());
  
  delay(1000);
  
  Serial.println("Łączenie z serwerem MySQL...");
  if (conn.connect(server_addr, 3306, user, password)) {
    Serial.println("POŁĄCZONO Z MYSQL!");
  } else {
    Serial.println("BŁĄD: Brak połączenia z serwerem bazy danych.");
  }
}

void loop() {
  if (conn.connected()) {
    delay(5000);
    
    // Przykład 1: Wstawianie do hello_arduino
    Serial.println("Zapisywanie do hello_arduino...");
    MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);
    cur_mem->execute(INSERT_HELLO);
    
    // Przykład 2: Wstawianie do vmeter (symulacja danych)
    Serial.println("Zapisywanie do vmeter...");
    int val0 = analogRead(A0);
    int val1 = analogRead(A1);
    int val2 = analogRead(A2);
    int val3 = analogRead(A3);
    int val4 = analogRead(A4);
    int val5 = analogRead(A5);
    
    sprintf(query, "INSERT INTO pszczolk_z12b.vmeter (v0, v1, v2, v3, v4, v5) VALUES (%d, %d, %d, %d, %d, %d)", val0, val1, val2, val3, val4, val5);
    cur_mem->execute(query);
    
    delete cur_mem;
    Serial.println("OK.");
  } else {
    Serial.println("Brak połączenia. Próba ponownego nawiązania...");
    if (conn.connect(server_addr, 3306, user, password)) {
      Serial.println("Ponownie połączono!");
    }
    delay(2000);
  }
}
