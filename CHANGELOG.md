# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

---

## [1.1.0] - 2026-03-31

### ✨ Melhorias

- Compatibilidade ampliada para **GLPI 10.x e 11.x**
- Nova integração da tela de configuração com o padrão mais recente do GLPI
- Leitura e persistência de configuração com fallback para compatibilidade legada
- Ajustes de instalação/migração para melhor suporte entre versões

### 🔧 Ajustes técnicos

- Registro da classe `PluginKeeppendingConfig` para integração com `Config`
- Atualização do `composer.json` para aceitar GLPI até `<12.0`
- Padronização do arquivo `inc/config.class.php` para ambientes Linux
- Documentação atualizada para a nova faixa de compatibilidade

---

## [1.0.0] - 2026-01-22

### ✨ Funcionalidades

- Mantém status "Pendente" em tickets automaticamente
- Diferenciação inteligente: manual vs automática
  - ✅ Permite mudanças manuais diretas
  - ❌ Bloqueia mudanças automáticas (respostas, emails)
- Sistema de logs para auditoria
- Página de configuração

### 📦 Instalação

- Instalação via wget simplificada
- Suporte a GLPI 10.0.0 até 10.9.x

### 🌍 Idiomas

- Português Brasileiro (pt_BR)
- Inglês (en_GB)

### 📚 Documentação

- README.md completo
- INSTALL.md com instruções detalhadas
- Guia de desenvolvimento de plugins GLPI

---

## Links

- [Repositório](https://github.com/gvcaetano190/keepPending)
- [Issues](https://github.com/gvcaetano190/keepPending/issues)
