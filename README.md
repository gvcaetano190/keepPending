# KeepPending - Plugin para GLPI 10.0.22

## 📌 Descrição

Plugin para GLPI que **mantém o status "Pendente" em chamados (tickets)** quando respostas são adicionadas, impedindo que tickets saiam automaticamente do status de pendência.

### ⚠️ Problema que Resolve

Em GLPI, quando alguém responde a um ticket que está em status **"Pendente"**, o sistema automaticamente pode alterar o status para outro estado. Isso pode causar:

- **Estouro de SLA** ❌ Tickets saem do status pendente e deixam de contar o tempo parado
- **Perda de rastreamento** ❌ Fica difícil saber quais chamados aguardam ação do cliente
- **Falha na lógica de negócio** ❌ Interrupção de processos que dependem do status pendente

### ✅ Solução

Este plugin **intercepta** qualquer tentativa de alterar o status de um ticket em "Pendente" e o mantém neste status, registrando a ação para auditoria.

---

## 🎯 Como Funciona

1. **Intercepta atualizações**: Hook `pre_item_update` captura antes de salvar
2. **Verifica status**: Confirma se o ticket está em "Pendente" (status = 5)
3. **Bloqueia mudanças**: Se alguém tentar alterar, restaura para "Pendente"
4. **Registra logs**: Mantém auditoria das tentativas de mudança
5. **Configurável**: Pode ser ativado/desativado conforme necessário

---

## 📦 Instalação

### Passo 1: Clonar o Plugin

```bash
cd /caminho/do/seu/glpi/plugins
git clone https://github.com/seu-usuario/glpi-keep-pending-status keeppendingstatus
```

Ou simplesmente descompacte a pasta do plugin neste diretório.

### Passo 2: Acessar Administração do GLPI

```
http://seu-glpi/front/plugin.php
```

### Passo 3: Localizar e Instalar

1. Procure por **"KeepPending"** na lista de plugins
2. Clique em **"Instalar"**
3. Aguarde a instalação ser concluída
4. Clique em **"Ativar"**

### Passo 4: Verificar Instalação

A tabela `glpi_plugin_keeppendingstatus_config` será criada automaticamente.

---

## ⚙️ Configuração

O plugin vem **pré-configurado** com:
- ✅ Manter status pendente **ATIVADO**
- ✅ Logs **ATIVADOS**

### Modificar Configurações (Futuro)

Você pode adicionar uma página de configuração em:
```
front/config.php
```

Para permitir ativar/desativar o comportamento sem desinstalar o plugin.

---

## 📋 Estrutura de Arquivos

```
glpi-keep-pending-status/
│
├── setup.php                    # Instalação/Desinstalação
├── hook.php                     # Hooks principais do plugin
├── README.md                    # Esta documentação
│
├── inc/
│   └── Config.class.php         # Classe de configuração
│
└── locales/
    ├── pt_BR.po                 # Tradução Português Brasileiro
    └── en_GB.po                 # Tradução Inglês
```

---

## 🔧 Detalhes Técnicos

### Status Padrão em GLPI

| Status | ID | Nome |
|--------|----|----|
| New | 1 | Novo |
| Assigned | 2 | Atribuído |
| Planned | 3 | Planejado |
| Waiting | 4 | Aguardando |
| **Pending** | **5** | **Pendente** |
| Solved | 6 | Resolvido |
| Closed | 7 | Fechado |

### Hooks Utilizados

#### `pre_item_update` (hook.php)
Executado **ANTES** de salvar a atualização
- Intercepta mudanças de status
- Restaura para "Pendente" se necessário
- Registra tentativas no log

#### `item_update` (hook.php)
Executado **APÓS** a atualização ser salva
- Validações finais
- Alertas adicionais (se necessário)

---

## 🔍 Como Verificar se Está Funcionando

### No GLPI:

1. Abra um ticket em status **"Pendente"**
2. Adicione uma resposta (comentário/followup)
3. Tente mudar o status para outro (ex: "Resolvido")
4. **Observe**: O status voltará para "Pendente" automaticamente

### Nos Logs:

Os eventos são registrados em:
```
GLPI → Configuração → Logs de eventos → Procure por "keepPendingStatus"
```

Você verá mensagens como:
```
[keepPending] Tentativa de alterar status bloqueada - Status pendente mantido. Alteração bloqueada: 5 → 6
```

---

## 🐛 Troubleshooting

### Plugin não aparece na lista
- Verifique se a pasta está em: `/caminho/glpi/plugins/keeppendingstatus`
- Certifique-se que os arquivos `setup.php` e `hook.php` existem
- Limpe o cache do GLPI (se aplicável)

### Erro ao instalar
```
Mensagem: "Table already exists"
```
- Verifique se a tabela foi criada em uma tentativa anterior
- Desinstale e reinstale o plugin

### Status continua mudando
- Verifique se o plugin está **ATIVADO** (não apenas instalado)
- Verifique os logs em: `GLPI → Configuração → Logs`
- Reinicie o servidor web

---

## 🔐 Segurança

- ✅ Não armazena credenciais
- ✅ Usa prepared statements para queries
- ✅ Registra todas as ações em log
- ✅ Respeita permissões do GLPI
- ✅ Compatível com GLPI 10.0.22+

---

## 📝 Changelog

### v1.0.0 (2026-01-22)
- ✨ Primeira versão
- ✅ Mantém status pendente em tickets
- ✅ Sistema de logs
- ✅ Suporte português e inglês

---

## 👨‍💻 Desenvolvedor

**Seu Nome**  
[seu-email@example.com](mailto:seu-email@example.com)  
[GitHub](https://github.com/seu-usuario)

---

## 📄 Licença

GPL v2 ou superior

Este plugin é software livre e pode ser modificado conforme a licença GPL v2.

---

## 🤝 Contribuições

Encontrou um bug ou tem uma sugestão? 
- Abra uma [issue no GitHub](https://github.com/seu-usuario/glpi-keep-pending-status/issues)
- Faça um [pull request](https://github.com/seu-usuario/glpi-keep-pending-status/pulls)

---

## 🔗 Links Úteis

- [Documentação GLPI Plugins](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/index.html)
- [GLPI Oficial](https://glpi-project.org/)
- [Marketplace GLPI](https://plugins.glpi-project.org/)
- [GitHub GLPI](https://github.com/glpi-project/glpi)

---

## ❓ FAQ

**P: Este plugin afeta todos os tickets?**  
R: Não. Afeta apenas tickets que estão atualmente em status "Pendente".

**P: Posso desabilitar o plugin sem perder dados?**  
R: Sim. Desative ou desinstale sem problemas. Os dados dos tickets permanecem intactos.

**P: O que acontece se o ticket não estiver em Pendente?**  
R: O plugin não faz nada. O status pode mudar normalmente.

**P: Os logs são limpidos automaticamente?**  
R: Não. Os logs ficam armazenados. Você pode limpá-los manualmente se necessário.

**P: Funciona com automações/workflows do GLPI?**  
R: Sim, mas o plugin tem prioridade. Se uma automação tentar mudar o status, ele será restaurado para Pendente.

---

**Versão**: 1.0.0  
**Compatibilidade**: GLPI 10.0.22+  
**PHP**: 8.0+  
**Data**: 22 de janeiro de 2026
