<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add unique index to help enforce booking constraints and improve performance
        Schema::table('car_rental_bookings', function (Blueprint $table) {
            $table->index(['vehicle_id', 'status', 'start_date', 'end_date'], 'idx_vehicle_booking_overlap');
        });

        // Database-specific constraint implementation
        if (DB::connection()->getDriverName() === 'mysql') {
            $this->createMySQLConstraints();
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            $this->createSQLiteConstraints();
        }
    }

    private function createMySQLConstraints(): void
    {
        // Create stored procedure to check for booking overlaps (MySQL version)
        DB::unprepared("
            DROP PROCEDURE IF EXISTS CheckBookingOverlap;

            CREATE PROCEDURE CheckBookingOverlap(
                IN p_vehicle_id BIGINT UNSIGNED,
                IN p_booking_id BIGINT UNSIGNED,
                IN p_start_date DATETIME,
                IN p_end_date DATETIME,
                IN p_status VARCHAR(255)
            )
            BEGIN
                DECLARE overlap_count INT DEFAULT 0;
                DECLARE overlap_message VARCHAR(500);

                -- Only check for overlaps if the booking is in an active state
                IF p_status IN ('confirmed', 'ongoing', 'pending') THEN
                    -- Count overlapping bookings
                    SELECT COUNT(*) INTO overlap_count
                    FROM car_rental_bookings
                    WHERE vehicle_id = p_vehicle_id
                    AND (p_booking_id IS NULL OR id != p_booking_id)  -- Exclude current record during updates
                    AND status IN ('confirmed', 'ongoing', 'pending')
                    AND deleted_at IS NULL
                    AND (
                        -- New booking starts during an existing booking
                        (p_start_date >= start_date AND p_start_date < end_date)
                        OR
                        -- New booking ends during an existing booking
                        (p_end_date > start_date AND p_end_date <= end_date)
                        OR
                        -- New booking completely encompasses an existing booking
                        (p_start_date <= start_date AND p_end_date >= end_date)
                    );

                    -- Throw error if overlap found
                    IF overlap_count > 0 THEN
                        SET overlap_message = 'Vehicle is already booked during the selected time period. Please choose different dates.';
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = overlap_message;
                    END IF;
                END IF;
            END
        ");

        // Create trigger for INSERT operations
        DB::unprepared('
            DROP TRIGGER IF EXISTS booking_overlap_check_insert;

            CREATE TRIGGER booking_overlap_check_insert
            BEFORE INSERT ON car_rental_bookings
            FOR EACH ROW
            BEGIN
                CALL CheckBookingOverlap(NEW.vehicle_id, NULL, NEW.start_date, NEW.end_date, NEW.status);
            END
        ');

        // Create trigger for UPDATE operations
        DB::unprepared('
            DROP TRIGGER IF EXISTS booking_overlap_check_update;

            CREATE TRIGGER booking_overlap_check_update
            BEFORE UPDATE ON car_rental_bookings
            FOR EACH ROW
            BEGIN
                CALL CheckBookingOverlap(NEW.vehicle_id, NEW.id, NEW.start_date, NEW.end_date, NEW.status);
            END
        ');
    }

    private function createSQLiteConstraints(): void
    {
        // SQLite doesn't support stored procedures, so we use triggers directly
        // Create trigger for INSERT operations
        DB::unprepared("
            DROP TRIGGER IF EXISTS booking_overlap_check_insert;

            CREATE TRIGGER booking_overlap_check_insert
            BEFORE INSERT ON car_rental_bookings
            FOR EACH ROW
            WHEN NEW.status IN ('confirmed', 'ongoing', 'pending')
            BEGIN
                SELECT CASE
                    WHEN (
                        SELECT COUNT(*)
                        FROM car_rental_bookings
                        WHERE vehicle_id = NEW.vehicle_id
                        AND status IN ('confirmed', 'ongoing', 'pending')
                        AND deleted_at IS NULL
                        AND (
                            (NEW.start_date >= start_date AND NEW.start_date < end_date)
                            OR
                            (NEW.end_date > start_date AND NEW.end_date <= end_date)
                            OR
                            (NEW.start_date <= start_date AND NEW.end_date >= end_date)
                        )
                    ) > 0
                    THEN RAISE(ABORT, 'Vehicle is already booked during the selected time period. Please choose different dates.')
                END;
            END;
        ");

        // Create trigger for UPDATE operations
        DB::unprepared("
            DROP TRIGGER IF EXISTS booking_overlap_check_update;

            CREATE TRIGGER booking_overlap_check_update
            BEFORE UPDATE ON car_rental_bookings
            FOR EACH ROW
            WHEN NEW.status IN ('confirmed', 'ongoing', 'pending')
            BEGIN
                SELECT CASE
                    WHEN (
                        SELECT COUNT(*)
                        FROM car_rental_bookings
                        WHERE vehicle_id = NEW.vehicle_id
                        AND id != NEW.id
                        AND status IN ('confirmed', 'ongoing', 'pending')
                        AND deleted_at IS NULL
                        AND (
                            (NEW.start_date >= start_date AND NEW.start_date < end_date)
                            OR
                            (NEW.end_date > start_date AND NEW.end_date <= end_date)
                            OR
                            (NEW.start_date <= start_date AND NEW.end_date >= end_date)
                        )
                    ) > 0
                    THEN RAISE(ABORT, 'Vehicle is already booked during the selected time period. Please choose different dates.')
                END;
            END;
        ");
    }

    public function down(): void
    {
        // Drop triggers first
        DB::unprepared('DROP TRIGGER IF EXISTS booking_overlap_check_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS booking_overlap_check_update');

        // Drop the stored procedure (MySQL only)
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::unprepared('DROP PROCEDURE IF EXISTS CheckBookingOverlap');
        }

        // Remove the index
        Schema::table('car_rental_bookings', function (Blueprint $table) {
            $table->dropIndex('idx_vehicle_booking_overlap');
        });
    }
};
