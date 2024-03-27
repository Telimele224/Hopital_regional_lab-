<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\User;

use Maatwebsite\Excel\Events\AfterSheet;


class Medecinlistes implements FromCollection
{
    public function collection()
    {
        return User::query()->join('medecins', 'users.id', '=', 'medecins.user_id')
        ->select('users.nom', 'users.prenom', 'users.telephone', 'users.adresse', 'medecins.specialite')
        ->get();

    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // $event->sheet->mergeCells('A1:B1'); // Fusionner les cellules A1 et B1
                $event->sheet->setCellValue('A1', 'Nom'); // Écrire "Nom" dans la cellule A1
                $event->sheet->setCellValue('B1', 'Prénom'); // Écrire "Prénom" dans la cellule C1
                $event->sheet->setCellValue('C1', 'telephone'); // Écrire "Prénom" dans la cellule C1
                $event->sheet->setCellValue('D1', 'adresse'); // Écrire "Prénom" dans la cellule C1
                $event->sheet->setCellValue('E1', 'speciate'); // Écrire "Prénom" dans la cellule C1


            },
        ];
    }
}
