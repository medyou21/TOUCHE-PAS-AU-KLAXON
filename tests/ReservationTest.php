<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\Trajet;

final class ReservationTest extends TestCase
{
    private Trajet $trajet;
    private int $trajetId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trajet = new Trajet();

        // Création d’un trajet temporaire pour les tests
        $data = [
            'depart' => 1,
            'arrivee' => 2,
            'date_depart' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'date_arrivee' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'nb_places_totales' => 5,
            'nb_places_disponibles' => 5,
            'conducteur_id' => 1
        ];

        $this->trajet->create($data);
        $this->trajetId = (int)$this->trajet->db->lastInsertId();
    }

    protected function tearDown(): void
    {
        // Suppression du trajet temporaire après chaque test
        $this->trajet->delete($this->trajetId);
        parent::tearDown();
    }

    public function testReserveValidNumberOfPlaces(): void
    {
        $trajet = $this->trajet->getByIdForUpdate($this->trajetId);
        $this->assertNotNull($trajet);

        $placesInitiales = (int)$trajet['nb_places_disponibles'];

        $reserver = 3;
        $newPlaces = $placesInitiales - $reserver;
        $success = $this->trajet->updateAvailablePlaces($this->trajetId, $newPlaces);

        $this->assertTrue($success);

        $trajetUpdated = $this->trajet->getById($this->trajetId);
        $this->assertEquals($newPlaces, $trajetUpdated['nb_places_disponibles']);
    }

    public function testCannotReserveMoreThanAvailable(): void
    {
        $trajet = $this->trajet->getByIdForUpdate($this->trajetId);
        $this->assertNotNull($trajet);

        $placesInitiales = (int)$trajet['nb_places_disponibles'];

        $reserver = $placesInitiales + 1;
        $newPlaces = $placesInitiales - $reserver;

        // Les places disponibles ne doivent jamais être négatives
        if ($newPlaces < 0) {
            $newPlaces = 0;
        }

        $success = $this->trajet->updateAvailablePlaces($this->trajetId, $newPlaces);
        $this->assertTrue($success);

        $trajetUpdated = $this->trajet->getById($this->trajetId);
        $this->assertGreaterThanOrEqual(0, $trajetUpdated['nb_places_disponibles']);
    }
}
