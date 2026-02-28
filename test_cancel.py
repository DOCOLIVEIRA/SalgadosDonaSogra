import urllib.request
import urllib.parse
import http.cookiejar
import re

# 1. Cria um cookie jar para manter a sessao
jar = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

# 2. Login
login_data = urllib.parse.urlencode({'username': 'admin', 'password': 'admin'}).encode()
resp = opener.open('http://127.0.0.1:5000/auth/login', login_data)
print('Login status:', resp.getcode())

# 3. Verifica estoque ANTES do cancelamento
resp2 = opener.open('http://127.0.0.1:5000/admin/produtos')
html = resp2.read().decode()
print('Estoque antes - HTML contém 450:', '450' in html)
print('Estoque antes - HTML contém 500:', '500' in html)

# 4. Cancela o pedido #1 via POST
cancel_data = urllib.parse.urlencode({}).encode()
try:
    resp3 = opener.open('http://127.0.0.1:5000/admin/pedidos/1/cancelar', cancel_data)
    print('Cancelamento status:', resp3.getcode())
except urllib.error.HTTPError as e:
    print('Cancelamento HTTPError:', e.code, e.reason)
except Exception as e:
    print('Cancelamento exception:', str(e)[:150])

# 5. Verifica estoque APOS cancelamento
resp4 = opener.open('http://127.0.0.1:5000/admin/produtos')
html2 = resp4.read().decode()
print('Estoque apos - HTML contém 500:', '500' in html2)
print('Estoque apos - HTML contém 450:', '450' in html2)

# 6. Verifica se pedido mostra Cancelado
resp5 = opener.open('http://127.0.0.1:5000/admin/')
html3 = resp5.read().decode()
print('Dashboard mostra Cancelado:', 'Cancelado' in html3)
print('Dashboard mostra admin (canceladoPor):', 'admin' in html3)

print()
print('=== RESULTADO DO TESTE ===')
print('Estoque restaurado para 500:', '500' in html2 and '450' not in html2)
print('Pedido cancelado no dashboard:', 'Cancelado' in html3)
