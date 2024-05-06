<?php

namespace App\Http\Controllers\Front_end;

use App\Http\Controllers\Controller;
use App\Models\Actualite;

use App\Mail\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Models\Galerie;
use App\Models\Medecin;
use App\Models\Personnel;
use App\Models\Service;
use App\Models\Temoignage;
use Chatify\Facades\ChatifyMessenger;
use Illuminate\Support\Facades\Auth;

class MenuNavigation extends Controller
{
    //
    public function apropos () {

        $personnels = Personnel::all();
        $temoignages = Temoignage::with('user.patient')->where('publier', true)->get();

            return view('Front_end.apropos', ['temoignages' => $temoignages,'personnels'=>$personnels]);
    }
    public function medecins () {

        $personnels = Personnel::all();
        return view('Front_end.medecins',[
            'personnels'=>$personnels
        ]);
    }
    public function essai () {

        return view('Front_end.show');
    }

    public function contact () {
        return view('Front_end.contact');

    }
    public function send(){
        $data = request()->validate([
            'name' =>'required|min:2',
            'email' =>'required|email',
            'telephone' =>'required|min:9',
            'message' =>'required|min:5',

        ]);
        Mail::to('receipentemail@gmail.com')->send(new ContactUs($data));

        // dd('sent');
        return redirect()->back()->with('success', 'Votre message a été envoyer avec succès ');
    }
    public function welcome()
    {
          // Récupérer les services depuis la base de données (limitez à 7 résultats)
        $services = Service::take(7)->get();
        $actualites = Actualite::all();
        $personnels = Personnel::all();
        $temoignages = Temoignage::with('user.patient')->where('publier', true)->get();
        return view('Front_end.welcome', ['temoignages' => $temoignages,
        'actualites' => $actualites, 'personnels'=>$personnels,'services' => $services

    ]);
    }

    public function departements () {
        $services = Service::all();
        return view('Front_end.les_departements', [
            'services' => $services
        ]);
    }
    public function chatify( $id = null)
    {
        $messenger_color = Auth::user()->messenger_color;
        return view('Chatify::app', [
            'id' => $id ?? 0,
            'messengerColor' => $messenger_color ? $messenger_color : ChatifyMessenger::getFallbackColor(),
            'dark_mode' => Auth::user()->dark_mode < 1 ? 'light' : 'dark',
        ]);
    }
    public function blog()
    {
        $actualites=Actualite::all();
        return view('Front_end.blog',compact('actualites'));
    }
    public function galerie()
    {
        $galeries=Galerie::all();
        return view('Front_end.galerie',compact('galeries'));
    }
    public function lien()
    {
        $personnels = Personnel::all();

        return view('Front_end.medecins', [
            'medecins' => Medecin::orderBy('created_at', 'desc')->paginate(10),
            'personnels'=>$personnels

        ]);
            }
        public function afficherMenu()
        {
            // Récupérer les services depuis la base de données (limitez à 7 résultats)
            $services = Service::take(7)->get();

            // Passer les données à la vue
            return view('Front_end.welcome', ['services' => $services]);
        }


}
