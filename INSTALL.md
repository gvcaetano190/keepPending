# Instalação - KeepPending Plugin

> Compatível com **GLPI 10.x** e **GLPI 11.x**.

## ⚡ Instalação Rápida (Um Comando)

```bash
cd /var/www/html/glpi/plugins && \
sudo wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keeppending.tar.gz && \
sudo tar -xzf keeppending.tar.gz && \
sudo mv keepPending-main keeppending && \
sudo rm keeppending.tar.gz && \
sudo chown -R www-data:www-data keeppending && \
sudo chmod -R 755 keeppending
```

---

## 📋 Instalação Passo a Passo

### 1. Acessar pasta de plugins

```bash
cd /var/www/html/glpi/plugins
```

### 2. Baixar o plugin

```bash
sudo wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keeppending.tar.gz
```

### 3. Descompactar

```bash
sudo tar -xzf keeppending.tar.gz
```

### 4. Renomear pasta

```bash
sudo mv keepPending-main keeppending
```

### 5. Limpar arquivo temporário

```bash
sudo rm keeppending.tar.gz
```

### 6. Ajustar permissões

```bash
sudo chown -R www-data:www-data keeppending
sudo chmod -R 755 keeppending
```

---

## 🔧 Ativar no GLPI

1. Acesse: `http://seu-glpi/front/plugin.php`
2. Localize **KeepPending** na lista
3. Clique em **Instalar**
4. Clique em **Ativar**

---

## ✅ Verificar Instalação

```bash
ls -la /var/www/html/glpi/plugins/keeppending/
```

Deve mostrar:
```
setup.php
hook.php
front/
inc/
locales/
README.md
...
```

---

## 🐛 Problemas Comuns

### Plugin não aparece na lista

- Verifique se a pasta é **`keeppending`** (minúsculas)
- Confirme que `setup.php` existe na pasta

### Erro de permissão

```bash
sudo chown -R www-data:www-data /var/www/html/glpi/plugins/keeppending
```

### Limpar e reinstalar

```bash
sudo rm -rf /var/www/html/glpi/plugins/keeppending
# Execute os comandos de instalação novamente
```

---

## 📖 Mais Informações

- [README.md](README.md) - Documentação principal
- [CHANGELOG.md](CHANGELOG.md) - Histórico de versões
