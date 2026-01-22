# 🚀 Instalação Rápida - KeepPending Plugin

## Comando Único (Copie e Cole)

```bash
cd /caminho/do/seu/glpi/plugins && \
wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keepPending.tar.gz && \
tar -xzf keepPending.tar.gz && \
mv keepPending-main keeppending && \
rm keepPending.tar.gz && \
chown -R www-data:www-data keeppending && \
chmod -R 755 keeppending && \
echo "✅ Plugin instalado com sucesso!"
```

**⚠️ IMPORTANTE**: Substitua `/caminho/do/seu/glpi` pelo caminho real do seu GLPI!

---

## Exemplo Prático

Se seu GLPI está em `/var/www/html/glpi`:

```bash
cd /var/www/html/glpi/plugins && \
wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keepPending.tar.gz && \
tar -xzf keepPending.tar.gz && \
mv keepPending-main keeppending && \
rm keepPending.tar.gz && \
chown -R www-data:www-data keeppending && \
chmod -R 755 keeppending && \
echo "✅ Plugin instalado com sucesso!"
```

---

## Próximos Passos

1. Acesse: `http://seu-glpi/front/plugin.php`
2. Procure por **"KeepPending"**
3. Clique em **"Instalar"**
4. Clique em **"Ativar"**

**Pronto! O plugin está funcionando!** 🎉

---

## Apenas o wget (Método Simples)

Se preferir fazer passo a passo:

```bash
# 1. Baixar
wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keepPending.tar.gz

# 2. Descompactar
tar -xzf keepPending.tar.gz

# 3. Renomear
mv keepPending-main keeppending

# 4. Ajustar permissões
chown -R www-data:www-data keeppending
chmod -R 755 keeppending
```

---

## Verificar se Funcionou

```bash
# Verificar se a pasta foi criada
ls -la /caminho/do/seu/glpi/plugins/keeppending

# Deve aparecer os arquivos:
# - setup.php
# - hook.php
# - README.md
# - inc/
# - locales/
```
