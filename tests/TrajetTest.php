<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\Trajet;

/**
 * @covers \App\Models\Trajet
 */
final class TrajetTest extends TestCase
{
    private Trajet $trajet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trajet = new Trajet();
    }

    public function testGetAvailableTrajetsReturnsArray(): void
    {
        $result = $this->trajet->getAvailableTrajets();
        $this->assertIsArray($result, 'getAvailableTrajets doit retourner un tableau');
    }

    public function testGetAgencesReturnsArray(): void
    {
        $agences = $this->trajet->getAgences();
        $this->assertIsArray($agences, 'getAgences doit retourner un tableau');
        if (!empty($agences)) {
            $this->assertArrayHasKey('id', $agences[0]);
            $this->assertArrayHasKey('nom_agence', $agences[0]);
        }
    }

    public function testCreateUpdateAndDelete(): void
    {
        // Données fictives pour création
        $data = [
            'depart' => 1,
            'arrivee' => 2,
            'date_depart' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'date_arrivee' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'nb_places_totales' => 5,
            'nb_places_disponibles' => 5,
            'conducteur_id' => 1
        ];

        // Création
        $created = $this->trajet->create($data);
        $this->assertTrue($created, 'La création du trajet doit réussir');

        // Récupération du dernier ID inséré
        $stmt = $this->trajet->getById((int)$this->trajet->db->lastInsertId());
        $this->assertIsArray($stmt, 'getById doit retourner un tableau');
        $id = (int)$stmt['id'];

        // Mise à jour
        $updateData = $data;
        $updateData['nb_places_totales'] = 10;
        $updateData['nb_places_disponibles'] = 8;
        $updated = $this->trajet->update($id, $updateData);
        $this->assertTrue($updated, 'La mise à jour du trajet doit réussir');

        $trajetUpdated = $this->trajet->getById($id);
        $this->assertEquals(10, $trajetUpdated['nb_places_totales']);
        $this->assertEquals(8, $trajetUpdated['nb_places_disponibles']);

        // Suppression
        $deleted = $this->trajet->delete($id);
        $this->assertTrue($deleted, 'La suppression du trajet doit réussir');

        $trajetDeleted = $this->trajet->getById($id);
        $this->assertNull($trajetDeleted, 'Le trajet supprimé ne doit plus exister');
    }

    public function testUpdateAvailablePlaces(): void
    {
        // Création d’un trajet temporaire
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
        $id = (int)$this->trajet->db->lastInsertId();

        $success = $this->trajet->updateAvailablePlaces($id, 3);
        $this->assertTrue($success);

        $trajet = $this->trajet->getById($id);
        $this->assertEquals(3, $trajet['nb_places_disponibles']);

        $this->trajet->delete($id);
    }

    public function testCountActifsReturnsInt(): void
    {
        $count = $this->trajet->countActifs();
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testGetTrajetsLastDaysReturnsArray(): void
    {
        $result = $this->trajet->getTrajetsLastDays(7);
        $this->assertIsArray($result);
        foreach ($result as $row) {
            $this->assertArrayHasKey('date', $row);
            $this->assertArrayHasKey('count', $row);
        }
    }
}
