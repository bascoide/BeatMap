# BeatMap - Plataforma Colaborativa de Artistas 🎵

BeatMap é uma plataforma web inovadora que conecta músicos e artistas, permitindo que descubram uns aos outros através de um mapa interativo geolocalizado. A plataforma oferece funcionalidades de rede social, perfis de artistas, chat global e sistema de avaliação.

## 🌟 Características Principais

- **Autenticação de Utilizadores** - Registo, login e recuperação de palavra-passe seguros
- **Perfis de Artistas** - Criação e edição de perfis detalhados com avatar, géneros e biografia
- **Mapa Interativo** - Visualização geolocalizada de artistas por distrito e município de Portugal
- **Sistema de Chat** - Chat global em tempo real entre utilizadores
- **Mensagens Privadas** - Comunicação privada entre artistas
- **Sistema de Upvote** - Apoie e descubra os melhores artistas da comunidade
- **Dashboard do Artista** - Gestão completa de perfil e conteúdo
- **Painel de Moderação** - Ferramentas administrativas para moderação de conteúdo
- **Confirmação de Email** - Sistema robusto de verificação de email
- **Upload de Avatar** - Gestão de imagens de perfil

## 🗂️ Estrutura do Projeto

```
BeatMap/
├── beatmap/                    # Núcleo principal da aplicação
│   ├── views/                  # Templates PHP de páginas
│   ├── inc/                    # Ficheiros de inclusão (config, header, footer)
│   ├── email/                  # Sistema de confirmação de email
│   ├── api/                    # Endpoints da API REST
│   ├── uploads/                # Diretório para uploads de ficheiros
│   ├── assets/                 # CSS e recursos estáticos
│   ├── sql/                    # Scripts de base de dados
│   └── data/                   # Dados geográficos (GeoJSON de Portugal)
├── beatmap(index)/             # Interface de login/registo
├── beatmap(mapa)/              # Aplicação do mapa interativo
├── index.php                   # Ponto de entrada principal
├── script.js                   # JavaScript global
└── style.css                   # Estilos globais
```

## 🚀 Instalação

### Pré-requisitos

- **PHP** 7.4 ou superior
- **MySQL** 5.7 ou superior
- **Apache** com mod_rewrite ativado
- **Composer** (para dependências PHP)

### Passos de Instalação

1. **Clone ou descarregue o repositório**
```bash
cd c:\xampp\htdocs
# Ou copie os ficheiros para a pasta BeatMap
```

2. **Configure o banco de dados**

```bash
# Aceda ao MySQL
mysql -u root -p

# Crie a base de dados
CREATE DATABASE beatmap;
USE beatmap;

# Importe os scripts SQL
SOURCE beatmap/sql/beatmap.sql;
SOURCE beatmap/sql/insert_artists.sql;
```

3. **Configure o ficheiro de configuração**

Edite `beatmap/inc/config.php` com suas credenciais:
```php
$servername = "localhost";
$username_db = "root";
$password_db = "sua_senha";  // Altere se necessário
$dbname = "beatmap";
```

4. **Configure o Sistema de Email**

Edite `beatmap/inc/mailer.php` com suas credenciais SMTP:
```php
$mail->Host = 'seu_servidor_smtp';
$mail->Username = 'seu_email@dominio.com';
$mail->Password = 'sua_senha_app';
```

5. **Defina permissões de pasta**

```bash
chmod 755 beatmap/uploads/
chmod 755 beatmap/uploads/avatars/
chmod 755 beatmap/inc/
```

## 💾 Base de Dados

O projeto utiliza MySQL com as seguintes tabelas principais:

- `users` - Contas de utilizadores
- `artists` - Perfis de artistas
- `artists_genres` - Géneros musicais dos artistas
- `auth_tokens` - Tokens de autenticação
- `global_chat` - Mensagens do chat global
- `private_messages` - Mensagens privadas entre utilizadores
- `artist_upvotes` - Sistema de votação
- `confirmation_log` - Histórico de confirmações de email
- `moderation_history` - Histórico de moderação

Para resetar a base de dados, execute:
```bash
mysql -u root beatmap < beatmap/sql/beatmap.sql
```

## 🔑 Configuração de Ambiente

### Variáveis Importantes

As seguintes variáveis podem ser configuradas em `beatmap/inc/config.php`:

| Variável | Descrição |
|----------|-----------|
| `BASE_URL` | URL base da aplicação |
| `ASSETS_URL` | Caminho para assets (CSS, JS) |
| `DEFAULT_AVATAR_PATH` | Avatar padrão para novos utilizadores |

## 📡 API REST

A aplicação disponibiliza os seguintes endpoints na pasta `beatmap/api/`:

### Autenticação
- `POST /api/login.php` - Login de utilizador
- `POST /api/logout.php` - Logout
- `GET /api/check_session.php` - Verifica sessão ativa

### Artistas
- `GET /api/get_artists.php` - Lista todos os artistas
- `GET /api/get_artist_profile.php` - Perfil de um artista
- `POST /api/upvote_artist.php` - Upvote em um artista
- `POST /api/report_artist.php` - Reportar artista

### Comunicação
- `GET /api/get_global_chat.php` - Obter mensagens do chat global
- `POST /api/send_global_chat.php` - Enviar mensagem no chat global
- `GET /api/get_private_messages.php` - Obter mensagens privadas
- `POST /api/send_private_message.php` - Enviar mensagem privada

## 🗺️ Dados Geográficos

O projeto inclui dados geográficos de Portugal em formato GeoJSON:

- `data/distritos_pt.geojson` - Distrito português
- `data/municipios_pt.geojson` - Municípios portugueses
- `data/distritos_index.json` - Índice de distritos
- `data/municipios_index.json` - Índice de municípios

## 📝 Uso

### Para Artistas

1. Aceda a `http://localhost/beatmap(index)/`
2. Crie uma conta ou faça login
3. Confirme seu email
4. Aceda a `http://localhost/beatmap(mapa)/` para explorar o mapa
5. Edite seu perfil no dashboard

### Para Administradores

1. Aceda ao painel de moderação em `beatmap/views/admin_moderation_history.php`
2. Revise relatórios de artistas
3. Tome ações moderativas conforme necessário

## 🔐 Segurança

- Senhas são hashadas com `password_hash()` (bcrypt)
- Tokens de autenticação com expiração
- Proteção contra SQL Injection com prepared statements
- Session management seguro
- Validação de entrada em todos os formulários

## 🛠️ Desenvolvimento

### Stack Tecnológico

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Mapas**: GeoJSON e Leaflet (recomendado)
- **Email**: PHPMailer

### Estrutura de Ficheiros Importantes

```
beatmap/
├── views/
│   ├── index.php               # Dashboard principal
│   ├── add_artist.php          # Adicionar artista
│   ├── edit_artist.php         # Editar perfil do artista
│   ├── artist_profile.php      # Visualizar perfil
│   ├── artist_grid_partial.php # Grid de artistas
│   └── list_artists.php        # Listar todos os artistas
├── inc/
│   ├── db.php                  # Conexão com BD
│   ├── header.php              # Header comum
│   ├── footer.php              # Footer comum
│   ├── mailer.php              # Configuração de email
│   └── available_genres.php    # Lista de géneros disponíveis
└── api/
    └── *.php                   # Endpoints da API
```

## 📦 Dependências Externas

- **PHPMailer** - Incluído em `beatmap/inc/PHPMailer/`
- **Leaflet** (opcional) - Para mapas interativos
- **OpenStreetMap** - Dados de mapas base

## 🐛 Resolução de Problemas

### Erro: "Base de dados não encontrada"
- Verifique se a base de dados foi criada
- Confirme as credenciais em `beatmap/inc/config.php`

### Erro: "Email não é enviado"
- Verifique as configurações SMTP em `beatmap/inc/mailer.php`
- Verifique os logs de email em `beatmap/inc/confirmation_log.txt`

### Erro: "Permissão negada para upload"
- Altere as permissões das pastas: `chmod 755 beatmap/uploads/`

### Sessão não persiste
- Verifique se `session_start()` está no início dos ficheiros
- Limpe cookies do navegador

## 📄 Licença

Este projeto é fornecido como está. Sinta-se livre para usar e modificar conforme suas necessidades.

## 👨‍💻 Contribuições

Para melhorias e correções:

1. Crie um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📧 Contato e Suporte

Para dúvidas ou sugestões, utilize o formulário de contacto disponível em `contact_send.php`.

## 🎯 Roadmap Futuro

- [ ] Integração com Spotify
- [ ] Sistema de notificações em tempo real
- [ ] Funcionalidade de colaboração entre artistas
- [ ] Integração de portfólio de música
- [ ] Sistema de eventos e shows
- [ ] Recomendações baseadas em IA
- [ ] App móvel (React Native)

---

**BeatMap** - Conectando artistas através da música 🎵

Versão: 1.0.0  
Última atualização: Maio de 2026
