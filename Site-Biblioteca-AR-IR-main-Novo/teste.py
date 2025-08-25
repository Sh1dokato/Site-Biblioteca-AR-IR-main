from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoAlertPresentException
import time

driver = webdriver.Chrome()

# Altera o caminho para a página de registro
driver.get("file:///C:/xampp/htdocs/Site-Biblioteca-AR-IR-main/Site-Biblioteca-AR-IR-main-Novo/Biblioteca%20Site%20P/registro.html")

time.sleep(1)

# Preenche o campo de CPF
cpf_input = driver.find_element(By.NAME, "cpf")
cpf_input.send_keys("12345678901")
time.sleep(1)

# Preenche o campo de telefone
telefone_input = driver.find_element(By.NAME, "telefone")
telefone_input.send_keys("11987654321")
time.sleep(1)

# Preenche o campo de senha
senha_input = driver.find_element(By.NAME, "senha")
senha_input.send_keys("123456")
time.sleep(1)

# Preenche o campo de nome
nome_input = driver.find_element(By.NAME, "nome")
nome_input.send_keys("Usuário Teste")
time.sleep(1)

# Clica no botão de registro
botao_registro = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
botao_registro.click()

# Aguarda e trata o alerta de erro (se aparecer)
try:
    # Aguarda até 5 segundos pelo alerta
    alert = WebDriverWait(driver, 5).until(EC.alert_is_present())
    alert_text = alert.text
    print(f"Alerta detectado: {alert_text}")
    
    # Aceita o alerta
    alert.accept()
    
    # Verifica se ainda está na página de registro
    if "registro.html" in driver.current_url:
        print("Ainda na página de registro após erro de servidor")
        print("Status: Registro falhou devido a erro de conexão com servidor")
    else:
        print("Página foi redirecionada apesar do erro")
        
except TimeoutException:
    print("Nenhum alerta apareceu - verificação direta")
except NoAlertPresentException:
    print("Nenhum alerta presente")

time.sleep(3)

# Verificação final do status
print("\n=== RESULTADO DO TESTE ===")
print(f"Título atual da página: {driver.title}")
print(f"URL atual: {driver.current_url}")

# Verifica se foi redirecionado para a página de login
if "login.html" in driver.current_url:
    print("✅ SUCESSO: Registro realizado e redirecionado para login.html!")
elif "registro.html" in driver.current_url:
    print("❌ FALHA: Ainda na página de registro - verificar conexão com servidor")
else:
    print(f"⚠️  STATUS DESCONHECIDO: Página atual não reconhecida")

# Encerra o navegador
# driver.quit()
