<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем ID мастеров для назначения
        $masters = DB::table('users')->where('role', 'master')->pluck('id')->toArray();

        DB::table('requests')->insert([
            [
                'clientName' => 'Смирнов Алексей Владимирович',
                'phone' => '+7 (999) 123-45-67',
                'address' => 'ул. Ленина, д. 10, кв. 25',
                'problemText' => 'Не работает стиральная машина, не сливает воду',
                'status' => 'new',
                'assignedTo' => null,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'clientName' => 'Кузнецова Елена Дмитриевна',
                'phone' => '+7 (999) 234-56-78',
                'address' => 'пр. Мира, д. 45, кв. 12',
                'problemText' => 'Течет кран на кухне, нужна замена смесителя',
                'status' => 'assigned',
                'assignedTo' => $masters[0] ?? null,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(5),
            ],
            [
                'clientName' => 'Васильев Дмитрий Николаевич',
                'phone' => '+7 (999) 345-67-89',
                'address' => 'ул. Гагарина, д. 78, кв. 5',
                'problemText' => 'Не включается холодильник, моргает лампочка',
                'status' => 'in_progress',
                'assignedTo' => $masters[1] ?? null,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subHours(2),
            ],
            [
                'clientName' => 'Николаева Татьяна Павловна',
                'phone' => '+7 (999) 456-78-90',
                'address' => 'ул. Советская, д. 23, кв. 41',
                'problemText' => 'Сломалась микроволновая печь, искрит внутри',
                'status' => 'done',
                'assignedTo' => $masters[2] ?? null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(1),
            ],
            [
                'clientName' => 'Морозов Сергей Александрович',
                'phone' => '+7 (999) 567-89-01',
                'address' => 'пр. Победы, д. 15, кв. 33',
                'problemText' => 'Заказчик передумал, отмена заявки',
                'status' => 'canceled',
                'assignedTo' => null,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(3),
            ],
            [
                'clientName' => 'Волкова Ольга Игоревна',
                'phone' => '+7 (999) 678-90-12',
                'address' => 'ул. Кирова, д. 56, кв. 18',
                'problemText' => 'Требуется установка посудомоечной машины',
                'status' => 'new',
                'assignedTo' => null,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
        ]);
    }
}
