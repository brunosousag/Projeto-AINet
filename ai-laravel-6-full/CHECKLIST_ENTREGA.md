# Checklist de entrega

## Base e estrutura

- [x] Projeto Laravel configurado.
- [x] Base de dados da docente mantida e usada com `migrate:fresh --seed`.
- [x] Seeders mantem utilizadores, clientes, catalogo, encomendas e recibos.
- [x] Modulos antigos fora do enunciado removidos: Teacher, Student, Course,
  Department, Discipline e Administrative.
- [x] Backup antigo removido ou identificado para remocao segura.

## Autenticacao e perfis

- [x] Login funcional.
- [x] Registo de clientes ativo.
- [x] Middleware/gates para administrador, funcionario e cliente.
- [x] Utilizadores bloqueados impedidos de entrar na aplicacao.
- [x] Perfil editavel para clientes e administradores.
- [x] Funcionarios apenas com area de seguranca, sem editar perfil completo.
- [x] Upload, substituicao e remocao segura da fotografia de perfil.

## Loja e cliente

- [x] Catalogo publico.
- [x] Catalogo simples: escolher primeiro a estampa e configurar depois.
- [x] Escolha da cor atraves de amostras clicaveis na pagina da t-shirt.
- [x] Pre-visualizacao da estampa recortada dentro da area imprimivel.
- [x] Ajuste da posicao, dimensao e opacidade da estampa durante a compra.
- [x] Carrinho preserva tamanho e resize escolhidos; permite atualizar apenas
  cor e quantidade.
- [x] Carrinho de compras.
- [x] Checkout com NIF, morada, metodo e referencia de pagamento.
- [x] Pagamento validado pela API externa simulada do enunciado.
- [x] Opcao para guardar dados de pagamento por defeito.
- [x] Area completa de imagens pessoais do cliente: consultar, adicionar,
  editar e remover.
- [x] Ajuste da posicao, dimensao e opacidade da estampa pessoal.
- [x] Cliente so ve as suas encomendas e os seus recibos.
- [x] Imagens pessoais privadas: acesso direto apenas pelo proprietario.

## Encomendas e recibos

- [x] Listagem de encomendas para clientes.
- [x] Funcionarios veem apenas encomendas pendentes.
- [x] Administradores veem todas as encomendas.
- [x] Filtragem por estado, cliente e data para administradores.
- [x] Cancelamento de encomendas pendentes.
- [x] Fecho de encomendas pendentes por administradores/funcionarios.
- [x] Geracao de PDF ao fechar uma encomenda.
- [x] PDF guardado em `storage/app/private/pdf_receipts`.
- [x] Pasta privada `storage/app/private/pdf_receipts` mantida numa copia limpa.
- [x] PDF detalhado com imagens embebidas atraves de Base64.
- [x] Botao de recibo na pagina de detalhe da encomenda.
- [x] Aba propria de recibos para clientes: `Store > My receipts`.
- [x] Aba propria de recibos para administradores:
  `Management > Receipts`.
- [x] Funcionarios sem acesso aos PDFs dos recibos.
- [x] Email automatico quando a encomenda e criada.
- [x] Email automatico quando a encomenda e cancelada.
- [x] Email automatico com recibo PDF anexado quando a encomenda e fechada.
- [x] Snapshot do design guardado por item para manter o historico imutavel.

## Administracao

- [x] Dashboard.
- [x] Gestao de categorias.
- [x] Gestao de imagens de catalogo.
- [x] Gestao de cores.
- [x] Upload da t-shirt base associada a cada cor.
- [x] Seletor visual com gradiente ao criar uma cor.
- [x] Preview e posicionamento da estampa de catalogo por administradores.
- [x] Gestao de precos.
- [x] Gestao de utilizadores.
- [x] Bloquear/desbloquear utilizadores.
- [x] Criar, editar, remover e alterar tipo dos colaboradores.
- [x] Listar, filtrar, bloquear e remover clientes sem expor o perfil privado.
- [x] Dashboard com vendas mensais, categorias, cores, tamanhos, clientes,
  imagens de catalogo/pessoais e cancelamentos.
- [x] Indicacao no carrinho de quantas unidades faltam para obter desconto.

## Validacao tecnica

- [x] Rotas principais organizadas.
- [x] Montra publica separada em `resources/views/shop`.
- [x] CRUD resource de imagens centralizado em `TshirtImageController`, com
  views em `resources/views/tshirt-images` e `TshirtImageFormRequest`.
- [x] Policy das imagens de t-shirt em `TshirtImagePolicy`.
- [x] CRUD resource de utilizadores centralizado em `UserController`, com
  `UserFormRequest`.
- [x] Modelos com `protected $fillable`, conforme os apontamentos.
- [x] Middleware de contas bloqueadas em `IsNotBlocked`.
- [x] Filtro de categorias extraido para o componente `Categories/FilterCard`.
- [x] Views compilaveis com `php artisan view:cache`.
- [x] Testes automatizados para pagamentos, recibos, permissoes, imagens e cores.
- [x] Smoke test HTTP do catalogo local com resposta `200`, categorias e previews.
- [x] Confirmacao visual manual no browser antes da entrega final.
