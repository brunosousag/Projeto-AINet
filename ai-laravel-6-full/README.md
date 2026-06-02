# FunShirt - Projeto AINet

Aplicacao Laravel para uma loja de t-shirts personalizadas. O projeto usa a base
de dados fornecida pela docente e foi adaptado para o dominio FunShirt, removendo
modulos antigos de escolas/cursos que nao pertencem ao enunciado.

## Arranque local

```bash
composer install
npm install
php artisan migrate:fresh --seed
npm run build
```

Com Laragon, o site pode ser aberto em:

```text
http://ai-laravel-6-full.test
```

Se o PHP da linha de comandos nao tiver SQLite ativo, ativar no `php.ini`:

```ini
extension=pdo_sqlite
extension=sqlite3
```

## Contas de teste

Todas as contas geradas pelo seeder usam a password:

```text
123
```

Contas faceis para testar:

- Administradores: `a1@mail.pt`, `a2@mail.pt`, `a3@mail.pt`
- Funcionarios: `f1@mail.pt`, `f2@mail.pt`, `f3@mail.pt`
- Clientes: `c1@mail.pt` ate `c10@mail.pt`

## Funcionalidades principais

- Catalogo publico de estampas: primeiro escolhe-se a imagem e depois a t-shirt.
- Escolha visual da cor atraves de amostras clicaveis na pagina da t-shirt.
- Pre-visualizacao da estampa recortada dentro da area imprimivel da t-shirt.
- Ajuste da posicao, dimensao e opacidade da estampa durante a compra.
- Carrinho com tamanho e composicao preservados; apenas cor e quantidade podem
  ser atualizadas depois de adicionar.
- Carrinho e checkout para clientes.
- Validacao do pagamento atraves da API externa simulada do enunciado.
- Area completa de imagens pessoais dos clientes, incluindo ajuste da estampa.
- Gestao de encomendas por administradores e funcionarios.
- Cancelamento de encomendas pendentes.
- Emails automaticos quando a encomenda fica pendente, cancelada ou fechada.
- Fecho de encomendas, geracao de recibo em PDF e envio automatico por email.
- Gestao de categorias, imagens de catalogo, cores e precos por administradores.
- Upload da t-shirt base associada a cada cor.
- Pre-visualizacao e posicionamento de estampas de catalogo por administradores.
- Gestao de clientes e colaboradores, com privacidade dos dados de cliente.
- Criacao, edicao e remocao de colaboradores por administradores.
- Upload e remocao segura da fotografia de perfil.
- Perfil editavel para clientes e administradores.
- Dashboard administrativo com estatisticas do historico da loja.
- Snapshot do design em cada compra para preservar o historico das encomendas.

## Estrutura alinhada com as fichas

O CRUD administrativo de imagens de t-shirt segue o padrao resource das fichas
e esta concentrado em:

```text
app/Http/Controllers/TshirtImageController.php
app/Http/Requests/TshirtImageFormRequest.php
app/Policies/TshirtImagePolicy.php
resources/views/tshirt-images/
```

A montra publica e uma utilizacao diferente das mesmas imagens e esta separada
em:

```text
resources/views/shop/
GET /shop
GET /shop/{tshirtImage}
```

O CRUD de utilizadores tambem segue o nome indicado nas fichas:

```text
app/Http/Controllers/UserController.php
app/Http/Requests/UserFormRequest.php
```

Os modelos declaram os campos editaveis atraves de `protected $fillable`, como
nos apontamentos da docente. O middleware que impede o acesso de contas
bloqueadas esta em `app/Http/Middleware/IsNotBlocked.php`.

O filtro de categorias reutiliza o componente indicado nas fichas:

```text
app/view/Components/Categories/FilterCard.php
resources/views/components/categories/filter-card.blade.php
```

## Recibos

Quando uma encomenda e fechada por um administrador ou funcionario, o PDF fica
guardado de forma privada em:

```text
storage/app/private/pdf_receipts/receipt_{order_id}.pdf
```

O PDF inclui os dados do cliente, entrega, pagamento, notas, linhas detalhadas,
imagens da t-shirt e da estampa, totais e paginacao. As imagens sao convertidas
para Data URI Base64 antes de serem embebidas no PDF, incluindo imagens pessoais
guardadas de forma privada.

O cliente recebe um email de agradecimento com o PDF anexado. Tambem consegue
abrir o recibo na propria encomenda e na aba:

```text
Store > My receipts
```

Administradores tambem tem uma aba propria:

```text
Management > Receipts
```

Funcionarios nao podem abrir recibos. Esta restricao e intencional e faz parte
do enunciado.

A rota tecnica para abrir um recibo e:

```text
/orders/{order}/receipt
```

## Servicos externos

O checkout usa por defeito:

```text
https://ainet-payments-api.vercel.app/api/payments
```

Pode ser substituido atraves de `PAYMENTS_API_URL`. Para testar emails no
Mailtrap, configurar `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`,
`MAIL_USERNAME` e `MAIL_PASSWORD` no ficheiro `.env`. `MAIL_TIMEOUT` limita a
espera quando o servidor SMTP nao responde.

## Validacao

Com SQLite ativo no PHP CLI:

```bash
php artisan route:list --except-vendor
php artisan view:cache
php artisan test
```

No Windows, se for preciso forcar as extensoes SQLite na linha de comandos:

```bash
php -d extension=pdo_sqlite -d extension=sqlite3 artisan test
```
