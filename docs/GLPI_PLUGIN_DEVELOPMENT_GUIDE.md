# 📚 Guia Completo: Como Criar um Plugin GLPI Funcional

> Este guia foi criado com base na documentação oficial do GLPI, plugins funcionais como [behaviors](https://github.com/InfotelGLPI/behaviors), e lições aprendidas no desenvolvimento do plugin keepPending.

---

## 📋 Índice

1. [Requisitos](#-requisitos)
2. [Estrutura de Pastas](#-estrutura-de-pastas)
3. [Nomenclatura (CRÍTICO!)](#-nomenclatura-crítico)
4. [Arquivos Obrigatórios](#-arquivos-obrigatórios)
5. [setup.php - Estrutura Completa](#-setupphp---estrutura-completa)
6. [hook.php - Estrutura Completa](#-hookphp---estrutura-completa)
7. [Página de Configuração](#-página-de-configuração)
8. [Hooks Disponíveis](#-hooks-disponíveis)
9. [Logging e Debug](#-logging-e-debug)
10. [Status de Tickets GLPI](#-status-de-tickets-glpi)
11. [Checklist Final](#-checklist-final)
12. [Erros Comuns](#-erros-comuns)
13. [Referências](#-referências)

---

## 📦 Requisitos

- **GLPI**: 10.0.0 ou superior
- **PHP**: 8.0 ou superior
- **Conhecimentos**: PHP básico, estrutura do GLPI

---

## 📁 Estrutura de Pastas

```
glpi/
└── plugins/
    └── meuplugin/              ← Nome da pasta (MINÚSCULAS!)
        ├── setup.php           ← OBRIGATÓRIO
        ├── hook.php            ← OBRIGATÓRIO
        ├── front/              ← Páginas frontend
        │   └── config.form.php ← Página de configuração
        ├── inc/                ← Classes PHP
        │   └── Config.class.php
        ├── locales/            ← Traduções
        │   ├── en_GB.po
        │   └── pt_BR.po
        ├── templates/          ← Templates Twig (opcional)
        ├── README.md           ← Documentação
        ├── CHANGELOG.md        ← Histórico de versões
        ├── LICENSE             ← Licença
        └── composer.json       ← Dependências (opcional)
```

---

## ⚠️ Nomenclatura (CRÍTICO!)

### Regra de Ouro: TUDO EM MINÚSCULAS

| Item | Formato | Exemplo |
|------|---------|---------|
| **Nome da pasta** | minúsculas | `meuplugin` |
| **Funções** | `plugin_*_nomedoplugin` | `plugin_init_meuplugin` |
| **Hooks** | `['nomedoplugin']` | `$PLUGIN_HOOKS['...']['meuplugin']` |
| **Tabelas** | `glpi_plugin_nomedoplugin_*` | `glpi_plugin_meuplugin_config` |

### ❌ ERRADO vs ✅ CORRETO

```php
// ❌ ERRADO - Não será reconhecido!
function plugin_init_MeuPlugin() { }
function plugin_MeuPlugin_install() { }
$PLUGIN_HOOKS['csrf_compliant']['MeuPlugin'] = true;

// ✅ CORRETO - Funciona!
function plugin_init_meuplugin() { }
function plugin_meuplugin_install() { }
$PLUGIN_HOOKS['csrf_compliant']['meuplugin'] = true;
```

---

## 📄 Arquivos Obrigatórios

### 1. `setup.php` - Funções Obrigatórias

| Função | Obrigatória | Descrição |
|--------|-------------|-----------|
| `plugin_init_NOME()` | ✅ SIM | Inicializa hooks do plugin |
| `plugin_version_NOME()` | ✅ SIM | Retorna informações do plugin |
| `plugin_NOME_check_prerequisites()` | Não | Verifica pré-requisitos |
| `plugin_NOME_check_config()` | Não | Verifica configuração |

### 2. `hook.php` - Funções Obrigatórias

| Função | Obrigatória | Descrição |
|--------|-------------|-----------|
| `plugin_NOME_install()` | ✅ SIM | Instalação (criar tabelas, etc) |
| `plugin_NOME_uninstall()` | ✅ SIM | Desinstalação (remover tabelas) |

---

## 📝 setup.php - Estrutura Completa

```php
<?php
/**
 * Plugin Setup File
 */

// Definir versão do plugin (boa prática)
define('PLUGIN_MEUPLUGIN_VERSION', '1.0.0');

/**
 * Inicializa os hooks do plugin - OBRIGATÓRIO
 * 
 * IMPORTANTE: Nome da função deve ser plugin_init_NOMEDOPLUGIN
 * onde NOMEDOPLUGIN é o nome da pasta em minúsculas
 */
function plugin_init_meuplugin() {
    global $PLUGIN_HOOKS;
    
    // ============================================
    // CSRF Compliance - OBRIGATÓRIO para GLPI 10+
    // ============================================
    $PLUGIN_HOOKS['csrf_compliant']['meuplugin'] = true;
    
    // ============================================
    // Página de Configuração (faz o nome ficar clicável)
    // ============================================
    $PLUGIN_HOOKS['config_page']['meuplugin'] = 'front/config.form.php';
    
    // ============================================
    // Hooks de Items - IMPORTANTE: usar ARRAY com itemtype!
    // ============================================
    
    // ⚠️ FORMATO CORRETO para GLPI 10:
    // Deve ser array associativo: ['Itemtype' => 'nome_da_funcao']
    
    // Antes de atualizar item (ex: Ticket)
    $PLUGIN_HOOKS['pre_item_update']['meuplugin'] = [
        'Ticket' => 'plugin_meuplugin_pre_item_update'
    ];
    
    // Depois de atualizar item
    $PLUGIN_HOOKS['item_update']['meuplugin'] = [
        'Ticket' => 'plugin_meuplugin_item_update'
    ];
    
    // Para múltiplos itemtypes:
    // $PLUGIN_HOOKS['pre_item_update']['meuplugin'] = [
    //     'Ticket'  => 'plugin_meuplugin_pre_item_update',
    //     'Problem' => 'plugin_meuplugin_pre_item_update',
    //     'Change'  => 'plugin_meuplugin_pre_item_update'
    // ];
}
    
    // Depois de deletar item
    // $PLUGIN_HOOKS['item_delete']['meuplugin'] = 'plugin_meuplugin_item_delete';
}

/**
 * Retorna informações do plugin - OBRIGATÓRIO
 * 
 * IMPORTANTE: Nome da função deve ser plugin_version_NOMEDOPLUGIN
 */
function plugin_version_meuplugin() {
    return [
        'name'           => 'Nome do Meu Plugin',        // Nome exibido
        'version'        => PLUGIN_MEUPLUGIN_VERSION,    // Versão
        'author'         => 'Seu Nome',                  // Autor
        'license'        => 'GPLv2+',                    // Licença
        'homepage'       => 'https://github.com/...',    // URL do projeto
        'requirements'   => [
            'glpi' => [
                'min' => '10.0.0',      // Versão mínima do GLPI
                'max' => '10.9.99',     // Versão máxima do GLPI
            ],
            'php' => [
                'min' => '8.0',         // Versão mínima do PHP
            ]
        ]
    ];
}

/**
 * Verifica pré-requisitos antes da instalação - OPCIONAL
 */
function plugin_meuplugin_check_prerequisites() {
    if (version_compare(GLPI_VERSION, '10.0.0', 'lt')) {
        echo "Este plugin requer GLPI >= 10.0.0";
        return false;
    }
    return true;
}

/**
 * Verifica se o plugin está configurado - OPCIONAL
 */
function plugin_meuplugin_check_config($verbose = false) {
    return true;
}
```

---

## 📝 hook.php - Estrutura Completa

```php
<?php
/**
 * Plugin Hooks File
 */

/**
 * Função de instalação do plugin - OBRIGATÓRIO
 * 
 * Executada quando o usuário clica em "Instalar"
 */
function plugin_meuplugin_install() {
    global $DB;
    
    // Criar tabela de configuração
    if (!$DB->tableExists('glpi_plugin_meuplugin_config')) {
        $query = "CREATE TABLE `glpi_plugin_meuplugin_config` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `config_option` tinyint(1) DEFAULT 1,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $DB->query($query);
        
        // Inserir configuração padrão
        if ($DB->tableExists('glpi_plugin_meuplugin_config')) {
            $DB->insert('glpi_plugin_meuplugin_config', [
                'config_option' => 1
            ]);
        }
    }
    
    return true;
}

/**
 * Função de desinstalação do plugin - OBRIGATÓRIO
 * 
 * Executada quando o usuário clica em "Desinstalar"
 */
function plugin_meuplugin_uninstall() {
    global $DB;
    
    // Remover tabela de configuração
    if ($DB->tableExists('glpi_plugin_meuplugin_config')) {
        $DB->query("DROP TABLE `glpi_plugin_meuplugin_config`");
    }
    
    return true;
}

/**
 * Hook pre_item_update - Executado ANTES de atualizar
 * 
 * Use $item->input para modificar dados antes de salvar
 * 
 * IMPORTANTE: O hook recebe o objeto $item diretamente
 */
function plugin_meuplugin_pre_item_update($item) {
    // Verificar tipo do item (se não usou array no registro do hook)
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    // Obter ID do item
    $item_id = $item->getID();
    
    // Obter dados do banco de dados (estado atual, antes da mudança)
    global $DB;
    $result = $DB->request([
        'SELECT' => ['status'],
        'FROM'   => 'glpi_tickets',
        'WHERE'  => ['id' => $item_id]
    ]);
    
    if ($result->count()) {
        $current_data = $result->current();
        $current_status = (int) $current_data['status'];
        
        // Verificar novo status que será aplicado
        $new_status = isset($item->input['status']) ? (int) $item->input['status'] : $current_status;
        
        // Modificar input ANTES de salvar
        // Exemplo: forçar status para Pendente
        // $item->input['status'] = 4;
    }
}

/**
 * Hook item_update - Executado DEPOIS de atualizar
 * 
 * Usado para logs, notificações, ações pós-salvamento
 */
function plugin_meuplugin_item_update($item) {
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    // Registrar log
    Toolbox::logInFile('meuplugin', "Ticket #{$item->getID()} foi atualizado\n");
}
```

---

## 🔍 Logging e Debug

### Usando Toolbox::logInFile (RECOMENDADO)

```php
// Escreve em /var/www/html/glpi/files/_log/meuplugin.log
Toolbox::logInFile('meuplugin', "Mensagem de log\n");

// Com variáveis
$ticket_id = 123;
$status = 4;
Toolbox::logInFile('meuplugin', "Ticket #$ticket_id alterado para status $status\n");
```

### Usando file_put_contents (para debug detalhado)

```php
$debug_file = GLPI_LOG_DIR . '/meuplugin_debug.log';
$timestamp = date('Y-m-d H:i:s');

file_put_contents($debug_file, "[$timestamp] Minha mensagem\n", FILE_APPEND);
```

### Visualizar logs em tempo real

```bash
# Ver últimas linhas
sudo tail -50 /var/www/html/glpi/files/_log/meuplugin.log

# Acompanhar em tempo real
sudo tail -f /var/www/html/glpi/files/_log/meuplugin.log

# Ver erros PHP do GLPI
sudo tail -f /var/www/html/glpi/files/_log/php-errors.log
```

### ⚠️ NÃO use Event::log diretamente

```php
// ❌ EVITAR - pode causar erro "Class Event not found"
Event::log(...);

// ❌ EVITAR - namespace pode mudar entre versões
\Glpi\Event::log(...);

// ✅ USAR - simples e funciona em todas as versões
Toolbox::logInFile('meuplugin', "mensagem\n");
```

---

## 🎫 Status de Tickets GLPI

### Constantes de Status (CommonITILObject)

| Constante | Valor | Nome PT-BR |
|-----------|-------|------------|
| `INCOMING` | 1 | Novo |
| `ASSIGNED` | 2 | Em atendimento (Processando) |
| `PLANNED` | 3 | Planejado |
| `WAITING` | 4 | **Pendente** |
| `SOLVED` | 5 | **Solucionado** |
| `CLOSED` | 6 | Fechado |

### Exemplo: Verificar e modificar status

```php
function plugin_meuplugin_pre_item_update($item) {
    // Constantes de status
    $PENDING_STATUS = 4;  // Pendente
    $SOLVED_STATUS = 5;   // Solucionado
    $ASSIGNED_STATUS = 2; // Em atendimento
    
    // Obter status atual do banco
    global $DB;
    $result = $DB->request([
        'SELECT' => ['status'],
        'FROM'   => 'glpi_tickets',
        'WHERE'  => ['id' => $item->getID()]
    ]);
    
    $current = $result->current();
    $current_status = (int) $current['status'];
    $new_status = (int) ($item->input['status'] ?? $current_status);
    
    // Exemplo: Se está Solucionado e tentando ir para Em Atendimento
    // redirecionar para Pendente
    if ($current_status === $SOLVED_STATUS && $new_status === $ASSIGNED_STATUS) {
        $item->input['status'] = $PENDING_STATUS;
    }
}
```

---

## ⚙️ Página de Configuração

### Arquivo: `front/config.form.php`

```php
<?php
include('../../../inc/includes.php');

// Verificar permissão
Session::checkRight('config', READ);

// Header do GLPI
Html::header(
    __('Meu Plugin', 'meuplugin'),
    $_SERVER['PHP_SELF'],
    'config',
    'plugins'
);

// Seu conteúdo HTML aqui
echo "<div class='center'>";
echo "<h2>Configurações do Plugin</h2>";
echo "</div>";

// Footer do GLPI
Html::footer();
```

---

## 🪝 Hooks Disponíveis

### Hooks de Items (CRUD)

| Hook | Quando Executa | Uso Comum |
|------|----------------|-----------|
| `pre_item_add` | Antes de criar | Validar/modificar dados |
| `item_add` | Depois de criar | Logs, notificações |
| `pre_item_update` | Antes de atualizar | Validar/modificar dados |
| `item_update` | Depois de atualizar | Logs, notificações |
| `pre_item_delete` | Antes de deletar | Validações |
| `item_delete` | Depois de deletar | Limpeza |
| `pre_item_purge` | Antes de purgar | Validações |
| `item_purge` | Depois de purgar | Limpeza |

### Outros Hooks Úteis

| Hook | Descrição |
|------|-----------|
| `config_page` | Define página de configuração |
| `menu_toadd` | Adiciona itens ao menu |
| `add_css` | Adiciona arquivos CSS |
| `add_javascript` | Adiciona arquivos JS |
| `display_central` | Exibe na página central |
| `post_init` | Executa após inicialização |

---

## ✅ Checklist Final

Antes de publicar seu plugin, verifique:

### Estrutura

- [ ] Pasta com nome em **minúsculas**
- [ ] `setup.php` existe
- [ ] `hook.php` existe
- [ ] `front/config.form.php` existe (para nome clicável)

### setup.php

- [ ] `plugin_init_NOME()` - nome em minúsculas
- [ ] `plugin_version_NOME()` - nome em minúsculas
- [ ] `$PLUGIN_HOOKS['csrf_compliant']['nome'] = true;`
- [ ] `$PLUGIN_HOOKS['config_page']['nome'] = '...';`
- [ ] Array `requirements` (não `minGlpiVersion`)
- [ ] Hooks de item usam **array com itemtype**: `['Ticket' => 'funcao']`

### hook.php

- [ ] `plugin_NOME_install()` retorna `true`
- [ ] `plugin_NOME_uninstall()` retorna `true`
- [ ] Funções de hooks implementadas
- [ ] Usa `Toolbox::logInFile()` para logs (não `Event::log`)

### Geral

- [ ] Testado em GLPI limpo
- [ ] README.md atualizado
- [ ] CHANGELOG.md criado
- [ ] Licença definida

---

## ❌ Erros Comuns

### 1. Plugin não aparece na lista

**Causa**: Nome de função errado

```php
// ❌ ERRADO
function plugin_init_MeuPlugin() { }
function plugin_MeuPlugin_getVersion() { }

// ✅ CORRETO
function plugin_init_meuplugin() { }
function plugin_version_meuplugin() { }
```

### 2. Plugin aparece cinza (não clicável)

**Causa**: Falta `config_page` hook

```php
// Adicione no plugin_init_NOME():
$PLUGIN_HOOKS['config_page']['meuplugin'] = 'front/config.form.php';
```

### 3. Erro "CSRF token invalid"

**Causa**: Falta declarar compliance CSRF

```php
// OBRIGATÓRIO no plugin_init_NOME():
$PLUGIN_HOOKS['csrf_compliant']['meuplugin'] = true;
```

### 4. "Plugin incompatível com esta versão"

**Causa**: Usando formato antigo de versão

```php
// ❌ ERRADO (formato antigo)
'minGlpiVersion' => '10.0.0',
'maxGlpiVersion' => '10.9.9',

// ✅ CORRETO (formato novo)
'requirements' => [
    'glpi' => [
        'min' => '10.0.0',
        'max' => '10.9.99',
    ]
]
```

### 5. Hook não é chamado

**Causa**: Hook registrado como string em vez de array

```php
// ❌ ERRADO - hook pode não ser chamado no GLPI 10
$PLUGIN_HOOKS['pre_item_update']['meuplugin'] = 'plugin_meuplugin_pre_item_update';

// ✅ CORRETO - usar array com itemtype
$PLUGIN_HOOKS['pre_item_update']['meuplugin'] = [
    'Ticket' => 'plugin_meuplugin_pre_item_update'
];
```

### 6. Erro "Class Event not found"

**Causa**: Usando Event::log que requer namespace

```php
// ❌ ERRADO - pode causar erro
Event::log(...);
\Glpi\Event::log(...);

// ✅ CORRETO - usar Toolbox
Toolbox::logInFile('meuplugin', "mensagem\n");
```

### 7. Erro de SQL / Query

**Causa**: Formato incorreto de query

```php
// ❌ ERRADO - formato antigo
$result = $DB->query("SELECT * FROM glpi_tickets WHERE id = $id");

// ✅ CORRETO - usar $DB->request()
$result = $DB->request([
    'SELECT' => ['status', 'name'],
    'FROM'   => 'glpi_tickets',
    'WHERE'  => ['id' => $id]
]);

if ($result->count()) {
    $data = $result->current();
    $status = $data['status'];
}
```

### 8. Tela branca ao acessar plugin

**Causa**: Erro PHP não tratado

**Solução**: Verificar logs

```bash
sudo tail -50 /var/www/html/glpi/files/_log/php-errors.log
```

---

## 🔄 Detectar Mudança Manual vs Automática

Para diferenciar se uma mudança foi feita manualmente pelo usuário ou automaticamente (resposta por email, etc):

```php
function plugin_meuplugin_isManualStatusChange($item) {
    global $DB;
    
    $ticket_id = $item->getID();
    $time_limit = date('Y-m-d H:i:s', strtotime('-30 seconds'));
    
    // Verificar se há followup recente (indica mudança automática)
    $recent_followup = $DB->request([
        'SELECT' => ['id', 'date_creation'],
        'FROM'   => 'glpi_itilfollowups',
        'WHERE'  => [
            'itemtype'      => 'Ticket',
            'items_id'      => $ticket_id,
            'date_creation' => ['>', $time_limit]
        ],
        'LIMIT'  => 1
    ]);
    
    if ($recent_followup->count() > 0) {
        return false; // Mudança automática (há followup recente)
    }
    
    return true; // Mudança manual
}
```

---

## 📖 Referências

- [Documentação Oficial GLPI Plugins](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/index.html)
- [Requirements (setup.php/hook.php)](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/requirements.html)
- [Plugin Example (oficial)](https://github.com/pluginsGLPI/example)
- [Plugin Behaviors (referência)](https://github.com/InfotelGLPI/behaviors)
- [Plugin keepPending (exemplo funcional)](https://github.com/gvcaetano190/keepPending)

---

## 📝 Template Rápido

Para criar um novo plugin rapidamente:

```bash
# 1. Criar estrutura
mkdir -p meuplugin/{front,inc,locales,docs}

# 2. Criar arquivos obrigatórios
touch meuplugin/setup.php
touch meuplugin/hook.php
touch meuplugin/front/config.form.php
touch meuplugin/README.md
touch meuplugin/CHANGELOG.md

# 3. Copiar conteúdo deste guia para os arquivos
# 4. Renomear "meuplugin" para o nome do seu plugin
# 5. Testar no GLPI
```

### Script de Deploy para Servidor

```bash
# Atualizar plugin no servidor GLPI
cd /var/www/html/glpi/plugins && \
sudo rm -rf meuplugin && \
sudo wget https://github.com/USUARIO/meuplugin/archive/refs/heads/main.tar.gz -O meuplugin.tar.gz && \
sudo tar -xzf meuplugin.tar.gz && \
sudo mv meuplugin-main meuplugin && \
sudo rm meuplugin.tar.gz && \
sudo chown -R www-data:www-data meuplugin
```

---

**Autor**: Gabriel Caetano  
**Baseado em**: Documentação oficial GLPI + Plugin Behaviors + Desenvolvimento keepPending  
**Última atualização**: Janeiro 2026
