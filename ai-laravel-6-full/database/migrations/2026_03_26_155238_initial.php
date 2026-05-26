<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
                // Cliente, Funcionario ou Administrador
                $table->enum('user_type', ['C', 'F', 'A']);

                // Acesso do utilizador blocked
                $table->enum('gender', ['M', 'F']);

                // Acesso do utilizador blocked
                $table->boolean('blocked')->default(false);

                // Fotografia/Avatar do utilizador
                $table->string('photo_url')->nullable();

                // custom data
                $table->json('custom')->nullable();

                // Utilizadores podem ser apagados com "soft deletes"
                $table->softDeletes();
            }
        );

        Schema::create('customers', function (Blueprint $table) {
            // Chave primário dos customers é a mesma que a chave primária dos users
            // (Customers é uma subclasse de Users)
            $table->bigInteger('id')->unsigned()->primary();
            $table->foreign('id')->references('id')->on('users');

            $table->string('nif', 9)->nullable();
            $table->text('address')->nullable();

            // VISA - Visa
            // MC - Master Card
            // PAYPAL - Paypal
            $table->enum('default_payment_type', ['Visa', 'PayPal', 'MB WAY'])->nullable();
            // Referência de pagamento varia consoante o payment type
            // Visa = Nº de cartão com 16 digitos
            // PayPal -> email
            // MB WAY -> Nº telemóvel com 9 digitos
            $table->string('default_payment_ref')->nullable();


            // custom data
            $table->json('custom')->nullable();

            // Customers podem ser apagados com "soft deletes"
            $table->softDeletes();
        });

        // configuração prices - só deverá ter uma linha que colorresponde à configuração atual
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->decimal('unit_price_catalog', 8, 2);
            $table->decimal('unit_price_own', 8, 2);
            $table->decimal('unit_price_catalog_discount', 8, 2);
            $table->decimal('unit_price_own_discount', 8, 2);
            // qty a partir da qual irá ser aplicado o discount (inclusive)
            $table->integer('qty_discount');

            // custom data
            $table->json('custom')->nullable();
        });

        // categories dos tshirtImages - para organizar as tshirtImages dentro do catálogo
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Fotografia/Representação da Categoria
            $table->string('image_url')->nullable();

            // custom data
            $table->json('custom')->nullable();

            // Categories de TshirtImages podem ser apagados com "soft deletes"
            $table->softDeletes();
        });

        // colors das t-shirts
        Schema::create('colors', function (Blueprint $table) {
            // Código da color colorresponde a um código de color em CSS
            $table->string('code', 50)->primary();
            $table->string('name');

            // custom data
            $table->json('custom')->nullable();

            // Colors das tshirts podem ser apagados com "soft deletes"
            $table->softDeletes();
        });

        Schema::create('tshirt_images', function (Blueprint $table) {
            $table->id();
            // Se customer_id = null, então tshirtImage faz parte do catálogo da loja
            // caso contrário, é uma tshirtImage próprio do customer - para usar exclusivamente nas suas t-shirts
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers');

            // A tshirtImage pode ou não pertencer a uma category.
            // Só as tshirtImages do catálogo da loja têm uma category
            // Se é uma tshirtImage próprio de um customer, então não tem category
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('categories');

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_url');

            // custom data
            $table->json('custom')->nullable();

            $table->timestamps();
            // TshirtImages podem ser apagados com "soft deletes"
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['pending', 'closed', 'canceled']);
            $table->bigInteger('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->date('date');
            $table->decimal('total_price', 8, 2);
            $table->text('notes')->nullable();
            $table->text('reason_for_cancellation')->nullable();

            $table->string('nif', 9);
            $table->text('address');
            // VISA - Visa
            // MC - Master Card
            // PAYPAL - Paypal
            $table->enum('payment_type', ['Visa', 'PayPal', 'MB WAY']);
            // Referência de pagamento varia consoante o payment type
            // Visa = Nº de cartão com 16 digitos
            // PayPal -> email
            // MB WAY -> Nº telemóvel com 9 digitos
            $table->string('payment_ref');

            // THe receipt/invoice document generated after order completion
            $table->string('receipt_url')->nullable();

            // custom data
            $table->json('custom')->nullable();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->bigInteger('tshirt_image_id')->unsigned();
            $table->foreign('tshirt_image_id')->references('id')->on('tshirt_images');
            $table->string('color_code', 50);
            $table->foreign('color_code')->references('code')->on('colors');
            $table->enum('size', ['XS', 'S', 'M', 'L', 'XL']);
            $table->integer('qty');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('sub_total', 8, 2);
            // custom data
            $table->json('custom')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('tshirt_images');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('customers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'blocked', 'photo_url', 'custom', 'deleted_at']);
        });
    }
};
