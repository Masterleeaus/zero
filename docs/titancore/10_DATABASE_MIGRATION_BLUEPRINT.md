# Titan Migration Blueprint

Example:

Schema::create('tz_signals', function (Blueprint $table) {

    $table->id();
    $table->uuid('company_id');
    $table->string('type');
    $table->json('payload');
    $table->timestamps();

});
