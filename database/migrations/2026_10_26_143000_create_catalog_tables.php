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
        // Add currency to site_settings if not present
        if (! Schema::hasColumn('site_settings', 'currency')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->string('currency', 10)->default('USD')->after('accent_color');
            });
        }

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index('sort');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->json('images')->nullable();
            $table->string('stock_status', 20)->default('in_stock');
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('sort')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();

            $table->index('is_published');
            $table->index('is_featured');
            $table->index('sort');
            $table->index('stock_status');
        });

        Schema::create('order_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->json('items');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('status', 20)->default('new');
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_requests');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');

        if (Schema::hasColumn('site_settings', 'currency')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
