<?php

namespace Modules\App\Database\Seeders;

use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Modules\App\Models\Membro;

class MembroSeeder extends Seeder
{
    /**
     * Popula `membros`: alguns registros fixos com aniversário hoje/nos
     * próximos dias (pra exercitar os filtros "Hoje"/"Este mês" sem esperar
     * o calendário virar) e o restante aleatório via Faker.
     */
    public function run(): void
    {
        $faker = FakerFactory::create('pt_BR');
        $hoje = Carbon::today();

        $fixos = [
            ['nome' => 'Maria Santos', 'nascimento' => $hoje->copy()->subYears(30), 'ignorar_ano' => true, 'ativo' => true, 'observacoes' => null],
            ['nome' => 'Rafael Oliveira', 'nascimento' => $hoje->copy()->addDays(4)->subYears(29), 'ignorar_ano' => false, 'ativo' => true, 'observacoes' => null],
            ['nome' => 'Ana Lima', 'nascimento' => $hoje->copy()->addDays(9)->subYears(26), 'ignorar_ano' => false, 'ativo' => true, 'observacoes' => 'Coordenadora de louvor'],
            ['nome' => 'Joana Ferreira', 'nascimento' => Carbon::parse('1985-11-15'), 'ignorar_ano' => true, 'ativo' => false, 'observacoes' => 'Saiu da equipe em março'],
        ];

        foreach ($fixos as $membro) {
            Membro::create([
                'nome' => $membro['nome'],
                'data_nascimento' => $membro['nascimento']->format('Y-m-d'),
                'ignorar_ano' => $membro['ignorar_ano'],
                'whatsapp' => '+55'.$faker->numerify('##9########'),
                'ativo' => $membro['ativo'],
                'observacoes' => $membro['observacoes'],
            ]);
        }

        for ($i = 0; $i < 20; $i++) {
            Membro::create([
                'nome' => $faker->name(),
                'data_nascimento' => $faker->dateTimeBetween('-70 years', '-15 years')->format('Y-m-d'),
                'ignorar_ano' => $faker->boolean(15),
                'whatsapp' => $faker->boolean(70) ? '+55'.$faker->numerify('##9########') : null,
                'ativo' => $faker->boolean(85),
                'observacoes' => $faker->boolean(25) ? $faker->sentence() : null,
            ]);
        }
    }
}
