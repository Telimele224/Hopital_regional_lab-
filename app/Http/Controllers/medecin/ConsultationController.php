<?php

namespace App\Http\Controllers\medecin;

use App\Http\Controllers\Controller;
use App\Http\Requests\medecin\ConsultationRequest;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Rdv;
use App\Models\TypeConsultation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('medecins.consultation.index', [
            'consultations' => Consultation::all(),
            'rdvs' => Rdv::all(),
            'patients' => Patient::all(),
            'typesConsultations' => TypeConsultation::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Récupérer le premier rendez-vous disponible
        $rdv = Rdv::first();

        // Récupérer l'utilisateur (patient) associé au rendez-vous
        $patient = $rdv->patient->user;

        // Passer les informations du patient à la vue
        $consultation = new Consultation();
        return view('medecins.consultation.form', [
            'rdv' => $rdv,
            'consultation' => $consultation,
            'patient' => $patient, // Passer les informations du patient à la vue
            'typesConsultations' => TypeConsultation::all(),
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(ConsultationRequest $request)
    {
        $data = $request->validated();
        Consultation::create($data);
        return redirect()->route('medecins.consultation.index')->with('success', 'patient consulté avec succes');
    }

    /**
     * Display the specified resource.
     */
    public function show(Consultation $consultation)
    {
        // Récupérer le rendez-vous associé à la consultation
        $rdv = $consultation->rdv;

        // Récupérer l'utilisateur (patient) associé au rendez-vous
        $patient = $rdv->patient->user;

        // Récupérer tous les types de consultations disponibles
        $typesConsultations = TypeConsultation::all();

        // Récupérer tous les patients pour le champ patient_id dans la vue
        $patients = Patient::all();

        // Passer les informations à la vue
        return view('medecins.consultation.show', [
            'rdv' => $rdv,
            'consultation' => $consultation,
            'patient' => $patient,
            'typesConsultations' => $typesConsultations,
            'patients' => $patients,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Consultation $consultation)
    {
        // Récupérer le rendez-vous associé à la consultation
        $rdv = $consultation->rdv;

        // Récupérer l'utilisateur (patient) associé au rendez-vous
        $patient = $rdv->patient->user;

        // Passer les informations à la vue
        return view('medecins.consultation.form', [
            'rdv' => $rdv,
            'consultation' => $consultation,
            'patient' => $patient,
            'typesConsultations' => TypeConsultation::all(),
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(ConsultationRequest $request, Consultation $consultation)
    {
        $data = $request->validated();
        $consultation->update($data);
        return redirect()->route('medecins.consultation.index')->with('success', 'Modification effectue avec succes');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultation $consultation)
    {
        //
    }


    public function listedesrendezvous(Request $request)
    {
        $user = auth()->user();

        // Vérifier si l'utilisateur est un médecin et s'il a un médecin associé
        if ($user->role === 'medecin' && $user->medecin) {
            // L'utilisateur est un médecin et a un médecin associé
            // Récupérer l'ID du médecin
            $medecinId = $user->medecin->id;

            // Récupérer les rendez-vous du médecin avec les détails des patients
            $rendezVous = Rdv::where('id_medecin', $medecinId)
                ->where('statut', 'accepté')
                ->with('patient.user')
                ->get();

            // Mettre à jour le statut des rendez-vous passés à "manqué"
            foreach ($rendezVous as $rdv) {
                if (Carbon::parse($rdv->dateRdv . ' ' . $rdv->heure)->isPast() && $rdv->statut != 'manqué') {
                    $rdv->statut = 'manqué';
                    $rdv->save();
                }
            }

            // Passer les rendez-vous à la vue
            return view('medecins.consultation.rendezvous', [
                'rendezVous' => $rendezVous,
                'mesRendezVous' => Rdv::all(),
                'consultations' => Consultation::all(),
                'patients' => Patient::all(),
                'typesConsultations' => TypeConsultation::all(),
                'selectedOption' => $request->get('filter', 'all'),
            ]);
        }
    }



    public function filterRendezVousByDate(Request $request)
    {
        $user = auth()->user();

        // Vérifier si l'utilisateur est un médecin et s'il a un médecin associé
        if ($user->role === 'medecin' && $user->medecin) {
            // L'utilisateur est un médecin et a un médecin associé
            // Récupérer l'ID du médecin
            $medecinId = $user->medecin->id;

            // Initialiser la requête pour récupérer les rendez-vous
            $query = Rdv::where('id_medecin', $medecinId)->where('statut', 'accepté')->with('patient.user');

            // Filtrer par date si une date est fournie
            if ($request->has('date') && !empty($request->date)) {
                $query->whereDate('dateRdv', $request->date);
            }

            // Récupérer les rendez-vous filtrés
            $rendezVous = $query->get();

            // Passer les rendez-vous et l'option sélectionnée à la vue
            return view('medecins.consultation.rendezvous', [
                'rendezVous' => $rendezVous,
                'mesRendezVous' => Rdv::all(),
                'consultations' => Consultation::all(),
                'patients' => Patient::all(),
                'typesConsultations' => TypeConsultation::all(),
                'selectedOption' => $request->get('filter', 'all'),
               // Définir et passer l'option sélectionnée à la vue
            ]);
        }

        return redirect()->back()->with('error', 'Accès non autorisé');
    }




}
