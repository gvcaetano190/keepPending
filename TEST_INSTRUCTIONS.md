# ✅ Plugin Corrigido - Instruções de Teste

Foram corrigidos os problemas de estrutura que impediam o GLPI de reconhecer o plugin.

## 🔧 O que foi corrigido:

1. ✅ Criado arquivo `init.php` - Necessário para inicialização correta
2. ✅ Criado arquivo `keeppending.php` - Entry point alternativo
3. ✅ Renomeadas todas as funções de `plugin_keepPending_` para `plugin_keeppending_`
4. ✅ Atualizado nomes de tabelas para minúsculas: `glpi_plugin_keeppending_config`
5. ✅ Adicionado arquivo `README.txt` para compatibilidade extra
6. ✅ Corrigido namespace da classe Config

## 📝 Nova Estrutura do Plugin:

```
keeppending/
├── setup.php              ✅ Funções de instalação
├── init.php               ✅ Novo - Inicialização
├── keeppending.php        ✅ Novo - Entry point
├── hook.php               ✅ Hooks (funções atualizadas)
├── README.md              ✅ Documentação
├── README.txt             ✅ Novo - Compatibilidade
├── INSTALL.md             ✅ Guia de instalação
├── CHANGELOG.md           ✅ Histórico
├── RELEASE_INSTRUCTIONS.md ✅ Instruções de release
├── LICENSE
├── composer.json
├── inc/
│   └── Config.class.php   ✅ Classe Config (atualizada)
└── locales/
    ├── pt_BR.po
    └── en_GB.po
```

## 🧪 Como Testar:

### Opção 1: Instalação Rápida (Recomendado)

```bash
# 1. Limpar instalação anterior (se houver)
rm -rf /var/www/html/glpi/plugins/keeppending

# 2. Baixar versão corrigida
cd /var/www/html/glpi/plugins
wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keeppending.tar.gz
tar -xzf keeppending.tar.gz
mv keepPending-main keeppending
rm keeppending.tar.gz

# 3. Ajustar permissões
chown -R www-data:www-data keeppending
chmod -R 755 keeppending
```

### Opção 2: Atualizar Local

Se você já tem uma pasta com o plugin antigo:

```bash
# Substituir pelos arquivos corrigidos
cd /caminho/para/keepPending
git pull origin main
```

## 🔍 Verificar no GLPI:

1. Acesse: `http://seu-glpi/front/plugin.php`
2. Procure por **"KeepPending"** na lista
3. Você deve ver agora:
   - ✅ Nome: KeepPending
   - ✅ Status: Disponível
   - ✅ Botão "Instalar"

## ✨ Próximas Ações:

1. **Instalar** o plugin no GLPI
2. **Ativar** o plugin
3. **Verificar logs** para confirmar que está funcionando
4. Criar um **novo ticket** em status "Pendente"
5. **Adicionar uma resposta** - o status deve manter "Pendente"

## 🐛 Se ainda não aparecer:

1. Limpe o cache do GLPI (se houver)
2. Reinicie o servidor web: `sudo systemctl restart apache2` ou `sudo systemctl restart nginx`
3. Verifique os logs do GLPI: `tail -f /var/www/html/glpi/files/_log/php-errors.log`
4. Confirme que a pasta está nomeada **exatamente** como: `/plugins/keeppending/` (minúsculas)

## 📋 Nomes Corretos Esperados:

- Diretório: `keeppending` ✅
- Funções: `plugin_keeppending_*` ✅
- Tabelas: `glpi_plugin_keeppending_*` ✅
- Classe: `Keeppending` ✅

---

Se tudo estiver correto, o plugin agora deve ser reconhecido! 🚀
