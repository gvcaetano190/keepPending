# 🚀 Como Criar a Release v1.0.0 no GitHub

A tag `v1.0.0` já foi criada e enviada para o GitHub! 🎉

Agora você precisa criar a **Release** no GitHub para deixar disponível para download.

---

## 📋 Passo a Passo

### 1️⃣ Acesse a página de Releases

Abra no navegador:
```
https://github.com/gvcaetano190/keepPending/releases
```

Ou clique em **"Releases"** no menu lateral direito do repositório.

---

### 2️⃣ Clique em "Draft a new release"

Ou em "Create a new release"

---

### 3️⃣ Preencha os Campos

#### 📌 **Choose a tag**
Selecione: `v1.0.0` (já está criada)

#### 📝 **Release title**
```
v1.0.0 - Primeira Versão Estável
```

#### 📄 **Describe this release**

Cole o texto abaixo (copie todo o conteúdo):

```markdown
# 🎉 KeepPending v1.0.0 - Primeira Versão Estável

Plugin para GLPI que **mantém o status "Pendente"** em chamados quando respostas são adicionadas automaticamente.

---

## ✨ Funcionalidades Principais

### 🔒 Mantém Status Pendente
- Impede que tickets saiam automaticamente do status "Pendente"
- Protege contra estouro de SLA
- Mantém rastreamento correto de tickets aguardando cliente

### 🧠 Diferenciação Inteligente
- ✅ **PERMITE** mudanças manuais diretas do campo status
- ❌ **BLOQUEIA** mudanças automáticas (respostas, emails, workflows)
- Detecta automaticamente o tipo de interação

### 📊 Sistema de Logs
- Registra todas as ações do plugin
- Auditoria completa no sistema de eventos do GLPI
- Rastreabilidade de todas as tentativas de mudança bloqueadas

---

## 📦 Instalação Rápida

### Comando Único (Recomendado)

```bash
cd /var/www/html/glpi/plugins && \
wget https://github.com/gvcaetano190/keepPending/archive/refs/tags/v1.0.0.tar.gz -O keepPending.tar.gz && \
tar -xzf keepPending.tar.gz && \
mv keepPending-1.0.0 keeppending && \
rm keepPending.tar.gz && \
chown -R www-data:www-data keeppending && \
chmod -R 755 keeppending
```

### Ou Passo a Passo

```bash
# 1. Baixar
wget https://github.com/gvcaetano190/keepPending/archive/refs/tags/v1.0.0.tar.gz

# 2. Descompactar
tar -xzf v1.0.0.tar.gz

# 3. Mover para plugins
mv keepPending-1.0.0 /var/www/html/glpi/plugins/keeppending

# 4. Ajustar permissões
chown -R www-data:www-data /var/www/html/glpi/plugins/keeppending
chmod -R 755 /var/www/html/glpi/plugins/keeppending
```

---

## 🎯 Ativação

1. Acesse: `http://seu-glpi/front/plugin.php`
2. Procure por **"KeepPending"**
3. Clique em **"Instalar"**
4. Clique em **"Ativar"**

---

## 📖 Exemplos de Uso

### ✅ Cenário 1: Mudança Manual (Permitida)
**Técnico abre ticket e altera apenas o campo Status**
- Resultado: Status muda normalmente ✅

### ❌ Cenário 2: Resposta Automática (Bloqueada)
**Técnico adiciona resposta e tenta mudar status junto**
- Resultado: Status permanece "Pendente" ❌

---

## 🔧 Requisitos

- **GLPI**: 10.0.0 até 10.9.9
- **PHP**: 8.0 ou superior
- **Banco**: MySQL/MariaDB

---

## 📚 Documentação Completa

- [README.md](https://github.com/gvcaetano190/keepPending/blob/main/README.md) - Documentação completa
- [INSTALL.md](https://github.com/gvcaetano190/keepPending/blob/main/INSTALL.md) - Guia de instalação
- [CHANGELOG.md](https://github.com/gvcaetano190/keepPending/blob/main/CHANGELOG.md) - Histórico de mudanças

---

## 🌍 Idiomas Suportados

- 🇧🇷 Português Brasileiro
- 🇬🇧 Inglês

---

## 🐛 Problemas?

Encontrou algum bug ou tem sugestões?
- [Abrir Issue](https://github.com/gvcaetano190/keepPending/issues)
- [Ver Documentação](https://github.com/gvcaetano190/keepPending)

---

## 📜 Licença

GPL v2 ou superior - Software Livre

---

**Desenvolvido com ❤️ para a comunidade GLPI**
```

---

### 4️⃣ Configurações Adicionais (Opcional)

- [ ] **Set as the latest release** - ✅ Marcar (é a primeira versão)
- [ ] **Set as a pre-release** - ❌ Deixar desmarcado
- [ ] **Create a discussion for this release** - ⚪ Opcional

---

### 5️⃣ Publicar

Clique no botão verde: **"Publish release"**

---

## ✅ Pronto!

Após publicar, a release estará disponível em:
```
https://github.com/gvcaetano190/keepPending/releases/tag/v1.0.0
```

E as pessoas poderão baixar diretamente:
```bash
wget https://github.com/gvcaetano190/keepPending/archive/refs/tags/v1.0.0.tar.gz
```

---

## 🎯 Links Úteis Depois da Release

- **Release**: https://github.com/gvcaetano190/keepPending/releases/tag/v1.0.0
- **Download direto**: https://github.com/gvcaetano190/keepPending/archive/refs/tags/v1.0.0.tar.gz
- **Repositório**: https://github.com/gvcaetano190/keepPending

---

**Dica**: Depois de criar a release, atualize o INSTALL.md e README.md para usar a URL da tag ao invés de `main`! 🚀
