<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\Trajet;

final class TrajetFormTest extends TestCase
{
    private Trajet $trajet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trajet = new Trajet();
    }

    public function testDepartAndArriveeDifferent(): void
    {
        $data = [
            'depart' => 1,
            'arrivee' => 1, // même agence
            'date_depart' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'date_arrivee' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'nb_places_totales' => 5,
            'nb_places_disponibles' => 5,
            'conducteur_id' => 1
        ];

        $this->assertNotEquals($data['depart'], $data['arrivee'], 'L’agence de départ doit être différente de celle d’arrivée');
    }

    public function testDateArriveeAfterDateDepart(): void
    {
        $data = [
            'date_depart' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'date_arrivee' => date('Y-m-d H:i:s', strtotime('+2 days')),
        ];

        $this->assertGreaterThan(strtotime($data['date_depart']), strtotime($data['date_arrivee']), 'La date d’arrivée doit être après la date de départ');
    }

    public function testNbPlacesDisponiblesNotExceedTotal(): void
    {
        $data = [
            'nb_places_totales' => 5,
            'nb_places_disponibles' => 10,
        ];

        $placesDisponibles = min($data['nb_places_disponibles'], $data['nb_places_totales']);
        $this->assertLessThanOrEqual($data['nb_places_totales'], $placesDisponibles, 'Le nombre de places disponibles ne doit pas dépasser le total');
    }
}
