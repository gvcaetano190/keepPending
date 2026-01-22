# 🚀 Instalação Corrigida - KeepPending Plugin

## ❌ O Erro:

O comando digitado tinha um erro:
```bash
sudo get https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz
```

**Problema**: `get` não é um comando. Deveria ser `wget`!

---

## ✅ Comando Correto:

### Opção 1: Script de Instalação Automático (Recomendado)

```bash
cd /var/www/html/glpi/plugins

# Baixar o script de instalação
wget https://raw.githubusercontent.com/gvcaetano190/keepPending/main/install.sh

# Executar com permissões
sudo bash install.sh

# Ou especificar outro caminho GLPI:
sudo bash install.sh /caminho/para/glpi
```

---

### Opção 2: Instalação Manual (Passo a Passo)

```bash
# 1. Ir para a pasta de plugins
cd /var/www/html/glpi/plugins

# 2. Baixar o plugin (WGET correto!)
sudo wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keeppending.tar.gz

# 3. Descompactar
sudo tar -xzf keeppending.tar.gz

# 4. Renomear a pasta
sudo mv keepPending-main keeppending

# 5. Remover arquivo compactado
sudo rm keeppending.tar.gz

# 6. Ajustar permissões
sudo chown -R www-data:www-data keeppending
sudo chmod -R 755 keeppending
```

---

### Opção 3: Uma Linha Única

```bash
cd /var/www/html/glpi/plugins && \
sudo wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keeppending.tar.gz && \
sudo tar -xzf keeppending.tar.gz && \
sudo mv keepPending-main keeppending && \
sudo rm keeppending.tar.gz && \
sudo chown -R www-data:www-data keeppending && \
sudo chmod -R 755 keeppending && \
echo "✓ Plugin instalado com sucesso!"
```

---

## 🔍 Verificar a Instalação:

```bash
# Listar arquivos do plugin
ls -la /var/www/html/glpi/plugins/keeppending/

# Deve aparecer:
# -rw-r--r--  setup.php
# -rw-r--r--  init.php
# -rw-r--r--  hook.php
# -rw-r--r--  keeppending.php
# -rw-r--r--  README.md
# drwxr-xr-x  inc/
# drwxr-xr-x  locales/
```

---

## 🎯 Próximo Passo:

Após instalar com sucesso, acesse o GLPI:

1. Abra: `http://seu-glpi/front/plugin.php`
2. Procure por **KeepPending**
3. Clique em **Instalar**
4. Clique em **Ativar**

---

## 📝 Lembre-se:

- Use **`wget`** não `get`
- Use **`sudo`** se precisar de permissões de admin
- A pasta deve ser nomeada **`keeppending`** (minúsculas)
- Ajuste as permissões corretamente para `www-data`

---

## 🆘 Se tiver problema novamente:

Mostre o erro completo e vou ajudar! 😊
