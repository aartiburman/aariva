<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (Schema::hasTable('kyc_documents')) {
    if (!Schema::hasColumn('kyc_documents', 'is_active')) {
        Schema::table('kyc_documents', function (Blueprint $table) {
            $table->integer('is_active')->default(1)->after('name');
        });
        echo "Column 'is_active' added to 'kyc_documents' table.\n";
    } else {
        echo "Column 'is_active' already exists in 'kyc_documents' table.\n";
    }
    
    if (!Schema::hasColumn('kyc_documents', 'created_at')) {
        Schema::table('kyc_documents', function (Blueprint $table) {
            $table->timestamps();
        });
        echo "Timestamps added to 'kyc_documents' table.\n";
    }
} else {
    Schema::create('kyc_documents', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->integer('is_active')->default(1);
        $table->timestamps();
    });
    echo "Table 'kyc_documents' created.\n";
}
