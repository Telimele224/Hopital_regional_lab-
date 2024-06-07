<?php

namespace App\Http\Controllers\patient\patientpdf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\View;
use App\Models\Rdv;
use Carbon\Carbon;
class patientPdf2Controller extends Controller
{
    public function generatepatientpdf()
    {
        // Récupérer tous les rendez-vous
        $rendezvous = Rdv::first(); // Vous pouvez ajuster cela en fonction de votre logique

        // Récupérer les informations du patient associé au rendez-vous
        $patient = $rendezvous->patient->user;

        // Récupérer les informations du médecin associé au rendez-vous
        $medecin = $rendezvous->medecin->user;

        // Numéro d'ordre du rendez-vous
        $orderNumber = $rendezvous->id; // Par exemple, l'ID du rendez-vous comme numéro d'ordre

        // Générez le HTML de la vue
        $html = View::make('patients.rendez-vous.patientpdf.pdf', compact('patient', 'medecin', 'rendezvous', 'orderNumber'))->render();

        // Instanciation de Dompdf
        $pdf = new Dompdf();

        // Charger le HTML
        $pdf->loadHtml($html);

        // (Optionnel) Configurer la taille et l'orientation de la page
        $pdf->setPaper('A4', 'portrait');

        // Rendre le PDF
        $pdf->render();

        // Retourne le PDF pour téléchargement
        return $pdf->stream('document.pdf');
    }
}
