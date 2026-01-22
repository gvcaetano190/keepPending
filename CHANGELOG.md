# CHANGELOG

## v1.0.0 (2026-01-22)

### ✨ Funcionalidades
- Implementação inicial do plugin
- Mantém status "Pendente" em tickets quando respostas são adicionadas
- Sistema de interceptação via hook `pre_item_update`
- Tabela de configuração criada automaticamente
- Suporte a logs de auditoria
- Suporte a múltiplos idiomas (Português Brasileiro e Inglês)

### 🔧 Técnico
- Implementação de setup.php com funções de install/uninstall
- Implementação de hook.php com lógica de bloqueio de status
- Classe Config para gerenciamento de configurações
- Função de logging para rastreabilidade
- Suporte a GLPI 10.0.22+
- Requer PHP 8.0+

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
