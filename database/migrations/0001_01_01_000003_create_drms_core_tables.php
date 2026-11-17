<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 6. Document Categories Table
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 120)->index();
            $table->text('description')->nullable();
            $table->string('color_code', 20)->default('#245B91');
            $table->unsignedInteger('retention_years')->default(5);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 7. Documents Table
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('internal_reference', 40)->unique();
            // Official reference preserved verbatim; intentionally NOT unique across external senders
            $table->string('official_reference', 150)->nullable()->index();
            $table->string('title', 255)->index();
            $table->text('description')->nullable();
            $table->string('organization', 200)->index();
            $table->string('sender', 200)->nullable()->index();
            $table->string('recipient', 200)->nullable()->index();
            $table->string('direction', 20)->default('incoming')->index(); // incoming, outgoing, internal
            $table->string('document_type', 50)->default('letter')->index(); // letter, invitation, memo, report, legal_notice, petition, circular, other
            $table->dateTime('event_date')->nullable()->index(); // Useful for invitations/events
            $table->date('date_issued')->nullable()->index();
            $table->date('date_received')->index();
            $table->foreignId('category_id')->constrained('document_categories')->restrictOnDelete();
            $table->foreignId('office_id')->constrained('offices')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('confidentiality_level', 30)->default('internal')->index(); // public, internal, confidential, strictly_confidential
            $table->string('status', 30)->default('registered')->index(); // draft, registered, in_review, actioned, archived
            $table->string('primary_file_path', 500)->nullable();
            $table->unsignedInteger('file_count')->default(0);
            $table->string('ocr_status', 25)->default('pending')->index(); // pending, processing, completed, failed, skipped
            $table->longText('ocr_text')->nullable();
            $table->text('ocr_error')->nullable();
            $table->timestamp('ocr_processed_at')->nullable();
            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('registered_at')->index();
            $table->boolean('is_archived')->default(false)->index();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            // Composite indexes for fast dashboard and duplicate queries
            $table->index(['office_id', 'is_archived', 'date_received'], 'idx_docs_office_archive_date');
            $table->index(['official_reference', 'organization'], 'idx_docs_duplicate_check');
        });

        // Add FULLTEXT index on MariaDB/MySQL for fast OCR and metadata search
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE documents ADD FULLTEXT INDEX ft_documents_search (official_reference, title, description, organization, sender, recipient, ocr_text)');
        }

        // 8. Document Files Table
        Schema::create('document_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('original_name', 255);
            $table->string('storage_disk', 30)->default('local');
            $table->string('storage_path', 500);
            $table->string('mime_type', 100);
            $table->string('extension', 15);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256_hash', 64)->index();
            $table->unsignedInteger('page_order')->default(1);
            $table->string('capture_source', 30)->default('upload'); // upload, camera_scan, external_scanner
            $table->string('ocr_status', 25)->default('pending')->index();
            $table->longText('file_ocr_text')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['document_id', 'page_order']);
        });

        // 9. Document Tags Table & Pivot
        Schema::create('document_tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 100)->index();
            $table->timestamps();
        });

        Schema::create('document_tag_pivot', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('document_tag_id')->constrained('document_tags')->cascadeOnDelete();
            $table->primary(['document_id', 'document_tag_id']);
        });

        // 10. Document Activity Logs Table (Append-Only Audit Trail)
        Schema::create('document_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->string('action', 80)->index();
            $table->string('severity', 20)->default('info')->index(); // info, warning, critical
            $table->string('description', 500);
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['document_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // 11. Document Assignments Table
        Schema::create('document_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('priority', 20)->default('normal')->index(); // normal, high, urgent
            $table->date('due_date')->nullable()->index();
            $table->text('instructions')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->string('status', 30)->default('pending')->index(); // pending, accepted, completed
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 12. Document Approvals Table
        Schema::create('document_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approver_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 30)->default('pending')->index(); // pending, approved, rejected, returned
            $table->text('comments')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        // 13. Document Archives Table
        Schema::create('document_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('archived_by')->constrained('users')->restrictOnDelete();
            $table->text('archive_reason');
            $table->string('archive_location_code', 80)->nullable();
            $table->date('retention_until')->nullable()->index();
            $table->timestamp('archived_at')->index();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('restore_reason')->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
        });

        // 14. Notifications Table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 15. System Settings Table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string'); // string, integer, boolean, json
            $table->string('group', 50)->default('general')->index();
            $table->string('description', 255)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 16. Backup Logs Table
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('backup_reference', 60)->unique();
            $table->string('type', 30)->default('full'); // full, database_only, files_only
            $table->string('storage_disk', 30)->default('local');
            $table->string('file_path', 500)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->char('sha256_hash', 64)->nullable();
            $table->boolean('is_encrypted')->default(true);
            $table->string('status', 30)->default('running')->index(); // running, completed, failed, verified
            $table->text('notes')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('document_archives');
        Schema::dropIfExists('document_approvals');
        Schema::dropIfExists('document_assignments');
        Schema::dropIfExists('document_activity_logs');
        Schema::dropIfExists('document_tag_pivot');
        Schema::dropIfExists('document_tags');
        Schema::dropIfExists('document_files');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_categories');
    }
};
