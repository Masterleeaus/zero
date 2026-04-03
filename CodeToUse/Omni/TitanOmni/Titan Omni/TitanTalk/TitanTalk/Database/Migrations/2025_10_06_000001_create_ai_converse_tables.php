<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('ai_converse_conversations', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('tenant_id')->nullable();
      $t->string('channel')->default('web');
      $t->string('external_ref')->nullable();
      $t->json('context')->nullable();
      $t->timestamps();
    });
    Schema::create('ai_converse_messages', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('tenant_id')->nullable();
      $t->unsignedBigInteger('conversation_id');
      $t->enum('sender',['user','bot']);
      $t->longText('text');
      $t->json('meta')->nullable();
      $t->timestamps();
      $t->index(['tenant_id','conversation_id']);
    });
    Schema::create('ai_converse_intents', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('tenant_id')->nullable();
      $t->string('name');
      $t->text('description')->nullable();
      $t->json('metadata')->nullable();
      $t->timestamps();
    });
    Schema::create('ai_converse_entities', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('tenant_id')->nullable();
      $t->string('name');
      $t->json('values')->nullable();
      $t->json('metadata')->nullable();
      $t->timestamps();
    });
    Schema::create('ai_converse_training_phrases', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('tenant_id')->nullable();
      $t->unsignedBigInteger('intent_id');
      $t->text('text');
      $t->json('metadata')->nullable();
      $t->timestamps();
      $t->index(['tenant_id','intent_id']);
    });
    Schema::create('ai_converse_dialogs', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('tenant_id')->nullable();
      $t->string('name');
      $t->json('graph')->nullable();
      $t->json('metadata')->nullable();
      $t->timestamps();
    });
    Schema::create('ai_converse_channels', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('tenant_id')->nullable();
      $t->string('name');
      $t->string('driver')->default('web');
      $t->json('config')->nullable();
      $t->boolean('enabled')->default(true);
      $t->timestamps();
    });
    Schema::create('ai_converse_provider_logs', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('tenant_id')->nullable();
      $t->unsignedBigInteger('conversation_id')->nullable();
      $t->enum('direction',['request','response']);
      $t->json('payload')->nullable();
      $t->json('meta')->nullable();
      $t->timestamps();
    });
  }
  public function down() {
    Schema::dropIfExists('ai_converse_provider_logs');
    Schema::dropIfExists('ai_converse_channels');
    Schema::dropIfExists('ai_converse_dialogs');
    Schema::dropIfExists('ai_converse_training_phrases');
    Schema::dropIfExists('ai_converse_entities');
    Schema::dropIfExists('ai_converse_intents');
    Schema::dropIfExists('ai_converse_messages');
    Schema::dropIfExists('ai_converse_conversations');
  }
};
