# KeepPending - Plugin para GLPI 10.0.22

## 📌 Descrição

Plugin para GLPI que **mantém o status "Pendente" em chamados (tickets)** quando respostas são adicionadas **automaticamente**, impedindo que tickets saiam do status de pendência sem intervenção manual.

### ⚠️ Problema que Resolve

Em GLPI, quando alguém responde a um ticket que está em status **"Pendente"**, o sistema automaticamente pode alterar o status para outro estado. Isso pode causar:

- **Estouro de SLA** ❌ Tickets saem do status pendente e deixam de contar o tempo parado
- **Perda de rastreamento** ❌ Fica difícil saber quais chamados aguardam ação do cliente
- **Falha na lógica de negócio** ❌ Interrupção de processos que dependem do status pendente

### ✅ Solução

Este plugin **intercepta APENAS mudanças automáticas** de status (respostas, emails, interações) e mantém o ticket em "Pendente", registrando a ação para auditoria.

### 🔓 Importante: Mudanças Manuais

**⚠️ O plugin NÃO interfere em mudanças manuais de status!**

- ✅ **PERMITE**: Quando você abre o ticket e muda o status manualmente no campo
- ✅ **PERMITE**: Mudanças diretas feitas por técnicos e gestores
- ❌ **BLOQUEIA**: Mudanças automáticas ao adicionar respostas ou comentários
- ❌ **BLOQUEIA**: Mudanças via email ou workflows automáticos

Isso garante controle total sobre o status quando necessário, mas impede que interações acidentalmente tirem o ticket de "Pendente".

---

## 🎯 Como Funciona

### Lógica de Detecção

O plugin diferencia entre mudanças **manuais** e **automáticas**:

#### ✅ Mudanças Manuais (PERMITIDAS - Plugin não interfere)
- Usuário acessa "Editar Ticket" e altera o campo Status diretamente
- Apenas o campo `status` é modificado
- **Resultado**: O status muda normalmente conforme solicitado

#### ❌ Mudanças Automáticas (BLOQUEADAS - Plugin mantém Pendente)
- Adicionar resposta/comentário ao ticket
- Registrar solução
- Adicionar tempo de ação
- Emails que atualizam o ticket
- Workflows e automações do GLPI
- **Resultado**: O status permanece em "Pendente" mesmo com a interação

### Funcionamento Técnico

1. **Intercepta atualizações**: Hook `pre_item_update` captura antes de salvar
2. **Verifica status atual**: Confirma se o ticket está em "Pendente" (status = 5)
3. **Detecta tipo de mudança**: Analisa quais campos estão sendo alterados
4. **Bloqueia se automático**: Se detectar campos de resposta/interação, mantém Pendente
5. **Permite se manual**: Se apenas status mudou, permite a alteração
6. **Registra logs**: Mantém auditoria das ações do plugin

---

## 📦 Instalação

> 💡 **Atalho**: Veja [INSTALL.md](INSTALL.md) para comando único de instalação!

### ⚡ Instalação Rápida (Um Comando)

```bash
cd /caminho/do/seu/glpi/plugins && \
wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keepPending.tar.gz && \
tar -xzf keepPending.tar.gz && \
mv keepPending-main keeppending && \
rm keepPending.tar.gz && \
chown -R www-data:www-data keeppending && \
chmod -R 755 keeppending && \
echo "Plugin instalado! Agora ative em: http://seu-glpi/front/plugin.php"
```

### Método 1: Download Direto (Passo a Passo)

```bash
# Navegue até a pasta de plugins do GLPI
cd /caminho/do/seu/glpi/plugins

# Baixe o plugin
wget https://github.com/gvcaetano190/keepPending/archive/refs/heads/main.tar.gz -O keepPending.tar.gz

# Descompacte
tar -xzf keepPending.tar.gz

# Renomeie a pasta
mv keepPending-main keeppending

# Remova o arAjustar Permissões

```bash
# Dar permissões adequadas
chown -R www-data:www-data /caminho/do/seu/glpi/plugins/keeppending
chmod -R 755 /caminho/do/seu/glpi/plugins/keeppending
```

### Passo 4: Localizar e Instalar

1. Acesse: `http://seu-glpi/front/plugin.php`
2. Procure por **"KeepPending"** na lista de plugins
3. Clique em **"Instalar"**
4. Aguarde a instalação ser concluída
5. Clique em **"Ativar"**

### Passo 5: Verificar Instalação

A tabela `glpi_plugin_keeppending_config` será criada automaticamente.

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
GLPI → Configuração → Logs de eventos → Procure por "keepPending"
```

Você verá mensagens como:
```
[keepPending] Mudança automática de status bloqueada - Interação detectada. Status mantido em Pendente: 5 → 2
```

---

## 📖 Exemplos Práticos

### ✅ Cenário 1: Mudança Manual (Plugin NÃO interfere)

**Situação**: Ticket #1234 está em status "Pendente" (aguardando cliente)

**Ação do Técnico**: 
1. Abre o ticket #1234
2. Altera o campo "Status" de "Pendente" para "Em atendimento"
3. Salva

**Resultado**: ✅ Status muda para "Em atendimento" normalmente  
**Motivo**: Plugin detecta que foi mudança manual direta e **permite**

---

### ❌ Cenário 2: Resposta com Status (Plugin interfere)

**Situação**: Ticket #5678 está em status "Pendente" (aguardando cliente)

**Ação do Técnico**:
1. Abre o ticket #5678
2. Adiciona uma resposta/comentário: "Aguardando retorno do cliente"
3. Tenta mudar o status para "Em atendimento" junto com a resposta
4. Salva

**Resultado**: ❌ Status permanece em "Pendente"  
**Motivo**: Plugin detecta que houve adição de conteúdo (resposta) junto com a mudança e **bloqueia** para manter a pendência

---

### 🎯 Como Mudar o Status Quando Necessário

Se você precisa tirar o ticket de "Pendente" depois de adicionar uma resposta:

1. **Primeiro**: Adicione a resposta/comentário e salve
2. **Depois**: Abra novamente o ticket
3. **Edite**: Mude APENAS o campo Status (sem adicionar resposta)
4. **Salve**: O plugin permitirá a mudança manual

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

### Status continua mudando mesmo com plugin ativo
- Verifique se o plugin está **ATIVADO** (não apenas instalado)
- Confirme se a mudança foi **manual direta** (nesse caso é esperado que mude)
- Verifique os logs em: `GLPI → Configuração → Logs de eventos`

### Não consigo mudar status de jeito nenhum
- O plugin só impede mudanças **automáticas** (com respostas/comentários)
- Para mudança **manual**: edite apenas o campo Status, sem adicionar conteúdo
- Se necessário, desative temporariamente o plugin

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

## � Licença

GPL v2 ou superior

Este plugin é software livre e pode ser modificado conforme a licença GPL v2.

---

## 🤝 Contribuições

Encontrou um bug ou tem uma sugestão? 
- Abra uma [issue no GitHub](https://github.com/gvcaetano190/keepPending/issues)
- Faça um [pull request](https://github.com/gvcaetano190/keepPending/pulls)

---

## 👨‍💻 Repositório

[https://github.com/gvcaetano190/keepPending](https://github.com/gvcaetano190/keepPending)

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
