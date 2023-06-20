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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('beds')->nullable();
            $table->unsignedSmallInteger('baths')->nullable();
            $table->unsignedSmallInteger('garage')->nullable();
            $table->unsignedSmallInteger('pool')->nullable();
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('county_id');
            $table->unsignedBigInteger('state_id');
            $table->string('address');
            $table->string('zip_code', 15);
            $table->json('description')->nullable();
            $table->enum('owner_wholesaler', ['owner', 'wholesaler'])->nullable();
            $table->decimal('asking_price', 8, 2)->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->integer('square_footage')->nullable();
            $table->enum('occupancy', ['owner occupied', 'occupied', 'rented', 'squatters', 'vacant', 'occupied by owner'])->nullable();
            $table->enum('ideal_selling_timeframe', ['asap', '30 days', '60 days', '90 days', '120 days', '120+ days'])->nullable();
            $table->enum('motivation', ['hot', 'warm', 'cold', 'dead'])->nullable();
            $table->enum('negotiable', ['yes', 'no'])->nullable();
            $table->text('violations')->nullable();
            $table->enum('mortgage', ['vacant', 'tenant_occupied', 'owner_occupied'])->nullable();
            $table->enum('listed_with_real_estate_agent', ['yes', 'no'])->nullable();
            $table->enum('repairs_needed', ['yes', 'no'])->nullable();
            $table->enum('property_condition', ['fair condition', 'fully renovated', 'needs full renovation'])->nullable();
            $table->string('how_long_you_owned')->nullable();
            $table->year('year_of_construction')->nullable();
            $table->enum('type_of_house', ['flat', 'condo_apartment', 'mansion', 'villa'])->nullable();
            $table->decimal('monthly_rental_amount', 8, 2)->nullable();
            $table->enum('status', ['available', 'sold', 'on hold']);
            $table->dateTime('expiration_time')->nullable();
            $table->unsignedBigInteger('currently_possessed_by')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->json('conversation')->nullable();
            $table->json('pictures')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('county_id')->references('id')->on('counties');
            $table->foreign('state_id')->references('id')->on('states');
            $table->foreign('currently_possessed_by')->references('id')->on('users');
            $table->foreign('seller_id')->references('id')->on('sellers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
