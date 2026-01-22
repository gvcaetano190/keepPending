# CHANGELOG

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

---

## [1.0.0] - 2026-01-22

### 🎉 Primeira Versão Estável

### ✨ Funcionalidades
- Implementação inicial do plugin
- Mantém status "Pendente" em tickets quando respostas são adicionadas
- **Diferenciação inteligente**: Detecta mudanças manuais vs automáticas
  - ✅ Permite mudanças manuais diretas do campo status
  - ❌ Bloqueia mudanças automáticas (respostas, emails, workflows)
- Sistema de interceptação via hook `pre_item_update`
- Tabela de configuração criada automaticamente
- Sistema de logs de auditoria completo
- Suporte a múltiplos idiomas (Português Brasileiro e Inglês)

### 🔧 Técnico
- Implementação de setup.php com funções de install/uninstall
- Implementação de hook.php com lógica de bloqueio de status
- Classe Config para gerenciamento de configurações
- Função de logging para rastreabilidade
- Suporte a GLPI 10.0.0 até 10.9.9
- Requer PHP 8.0+

### 📦 Instalação
- Instalação via wget simplificada
- Comando único de instalação
- Documentação completa (README.md e INSTALL.md)

### 📚 Documentação
- README.md completo com exemplos práticos
- INSTALL.md com instruções de instalação rápida
- Comentários detalhados no código
- Exemplos de cenários de uso (manual vs automático)

### 📖 Documentação
- README.md completo com instruções de instalação
- FAQ com respostas comuns
- Documentação técnica detalhada
- Exemplos de uso

### 🐛 Bugs Conhecidos
- Nenhum identificado na v1.0.0

### 🗺️ Roadmap Futuro
- [ ] Página de configuração front-end
- [ ] Dashboard com estatísticas de bloqueios
- [ ] Opção de whitelist de usuários que podem mudar status
- [ ] Relatórios de tentativas de mudança
- [ ] Suporte para mais idiomas

---

## Versionamento

Este plugin segue [Semantic Versioning](https://semver.org/):
- **MAJOR**: Alterações incompatíveis com versões anteriores
- **MINOR**: Novas funcionalidades compatíveis
- **PATCH**: Correções de bugs e melhorias menores
