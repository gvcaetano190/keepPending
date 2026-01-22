# KeepPending - Plugin GLPI

Plugin para GLPI que **mantém o status "Pendente" em chamados** quando respostas são adicionadas automaticamente.

[![GLPI Version](https://img.shields.io/badge/GLPI-10.0.0--10.9.x-blue.svg)](https://glpi-project.org/)
[![License](https://img.shields.io/badge/License-GPLv2+-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange.svg)](CHANGELOG.md)

---

## 📌 Problema que Resolve

Quando alguém responde a um ticket em status **"Pendente"**, o GLPI pode alterar automaticamente o status. Isso causa:

- ❌ **Estouro de SLA** - Tickets saem do status pendente
- ❌ **Perda de rastreamento** - Difícil saber quais aguardam cliente
- ❌ **Falha na lógica de negócio** - Interrupção de processos

## ✅ Solução

O plugin **intercepta APENAS mudanças automáticas** e mantém o ticket em "Pendente":

| Tipo de Mudança | Comportamento |
|-----------------|---------------|
| ✅ Manual (editar status diretamente) | **Permitido** |
| ❌ Automática (resposta, email, etc) | **Bloqueado** |

---

## 📦 Instalação Rápida

```bash
cd /var/www/html/glpi/plugins && \
sudo wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keeppending.tar.gz && \
sudo tar -xzf keeppending.tar.gz && \
sudo mv keepPending-main keeppending && \
sudo rm keeppending.tar.gz && \
sudo chown -R www-data:www-data keeppending && \
sudo chmod -R 755 keeppending
```

Depois:
1. Acesse: `http://seu-glpi/front/plugin.php`
2. Clique em **Instalar** no KeepPending
3. Clique em **Ativar**

> 📖 Instruções detalhadas em [INSTALL.md](INSTALL.md)

---

## 🎯 Como Funciona

### Mudanças Manuais (PERMITIDAS)
```
Usuário → Abre ticket → Altera campo Status → Salva
Resultado: ✅ Status muda normalmente
```

### Mudanças Automáticas (BLOQUEADAS)
```
Usuário → Abre ticket → Adiciona resposta + tenta mudar status → Salva
Resultado: ❌ Status permanece "Pendente"
```

### Para Mudar Status Após Resposta

1. Adicione a resposta e salve
2. Abra o ticket novamente
3. Mude **apenas** o campo Status
4. Salve - o plugin permite

---

## 📋 Estrutura do Plugin

```
keeppending/
├── setup.php           # Inicialização e versão
├── hook.php            # Hooks de install/uninstall e lógica
├── front/
│   └── config.form.php # Página de configuração
├── inc/
│   └── Config.class.php
├── locales/
│   ├── en_GB.po
│   └── pt_BR.po
├── README.md
├── INSTALL.md
├── CHANGELOG.md
└── LICENSE
```

---

## 🔧 Requisitos

- **GLPI**: 10.0.0 a 10.9.x
- **PHP**: 8.0+
- **Banco**: MySQL/MariaDB

---

## 📚 Documentação

| Documento | Descrição |
|-----------|-----------|
| [INSTALL.md](INSTALL.md) | Guia de instalação |
| [CHANGELOG.md](CHANGELOG.md) | Histórico de versões |
| [docs/GLPI_PLUGIN_DEVELOPMENT_GUIDE.md](docs/GLPI_PLUGIN_DEVELOPMENT_GUIDE.md) | Guia de desenvolvimento de plugins GLPI |

---

## 🐛 Problemas?

- **Plugin não aparece**: Verifique se a pasta é `keeppending` (minúsculas)
- **Erro ao instalar**: Verifique permissões (`www-data`)
- **Status continua mudando**: Confirme que o plugin está **ativo**

Abra uma [issue](https://github.com/gvcaetano190/keepPending/issues) se precisar de ajuda.

---

## 📄 Licença

GPLv2+ - [LICENSE](LICENSE)

---

## 👨‍💻 Autor

**Gabriel Caetano**

- GitHub: [@gvcaetano190](https://github.com/gvcaetano190)
- Repositório: [gvcaetano190/keepPending](https://github.com/gvcaetano190/keepPending)
