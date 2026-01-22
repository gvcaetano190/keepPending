# 📚 Guia Completo: Como Criar um Plugin GLPI Funcional

> Este guia foi criado com base na documentação oficial do GLPI e em plugins funcionais como [behaviors](https://github.com/InfotelGLPI/behaviors).

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
9. [Checklist Final](#-checklist-final)
10. [Erros Comuns](#-erros-comuns)
11. [Referências](#-referências)

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
    // Hooks de Items (opcional)
    // ============================================
    
    // Antes de adicionar item
    // $PLUGIN_HOOKS['pre_item_add']['meuplugin'] = 'plugin_meuplugin_pre_item_add';
    
    // Depois de adicionar item
    // $PLUGIN_HOOKS['item_add']['meuplugin'] = 'plugin_meuplugin_item_add';
    
    // Antes de atualizar item
    $PLUGIN_HOOKS['pre_item_update']['meuplugin'] = 'plugin_meuplugin_pre_item_update';
    
    // Depois de atualizar item
    $PLUGIN_HOOKS['item_update']['meuplugin'] = 'plugin_meuplugin_item_update';
    
    // Antes de deletar item
    // $PLUGIN_HOOKS['pre_item_delete']['meuplugin'] = 'plugin_meuplugin_pre_item_delete';
    
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
 */
function plugin_meuplugin_pre_item_update($item) {
    // Verificar tipo do item
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    // Sua lógica aqui...
    // Exemplo: modificar input antes de salvar
    // $item->input['campo'] = 'novo_valor';
}

/**
 * Hook item_update - Executado DEPOIS de atualizar
 */
function plugin_meuplugin_item_update($item) {
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    // Sua lógica aqui (logs, notificações, etc)
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

### hook.php

- [ ] `plugin_NOME_install()` retorna `true`
- [ ] `plugin_NOME_uninstall()` retorna `true`
- [ ] Funções de hooks implementadas

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

### 5. Hooks não funcionam

**Causa**: Nome do plugin diferente nos hooks

```php
// ❌ ERRADO - nomes diferentes
$PLUGIN_HOOKS['pre_item_update']['MeuPlugin'] = '...';
$PLUGIN_HOOKS['item_update']['meuplugin'] = '...';

// ✅ CORRETO - mesmo nome em todos
$PLUGIN_HOOKS['pre_item_update']['meuplugin'] = '...';
$PLUGIN_HOOKS['item_update']['meuplugin'] = '...';
```

---

## 📖 Referências

- [Documentação Oficial GLPI Plugins](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/index.html)
- [Requirements (setup.php/hook.php)](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/requirements.html)
- [Plugin Example (oficial)](https://github.com/pluginsGLPI/example)
- [Plugin Behaviors (referência)](https://github.com/InfotelGLPI/behaviors)

---

## 📝 Template Rápido

Para criar um novo plugin rapidamente:

```bash
# 1. Criar estrutura
mkdir -p meuplugin/{front,inc,locales}

# 2. Criar arquivos obrigatórios
touch meuplugin/setup.php
touch meuplugin/hook.php
touch meuplugin/front/config.form.php

# 3. Copiar conteúdo deste guia para os arquivos
# 4. Renomear "meuplugin" para o nome do seu plugin
# 5. Testar no GLPI
```

---

**Autor**: Gabriel Caetano  
**Baseado em**: Documentação oficial GLPI + Plugin Behaviors  
**Última atualização**: Janeiro 2026
