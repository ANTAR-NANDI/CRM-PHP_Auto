<?php

namespace Database\Seeders;

use App\Models\GenericName;
use Illuminate\Database\Seeder;

class PharmacyGenericNamesSeeder extends Seeder
{
    /** Seed the supplied 500 generic medicine names without duplicates. */
    public function run(): void
    {
        $names = preg_split('/\R/', trim(<<<'NAMES'
Paracetamol
Ibuprofen
Naproxen
Aceclofenac
Diclofenac Sodium
Ketorolac
Meloxicam
Piroxicam
Tenoxicam
Etoricoxib
Celecoxib
Aspirin
Tramadol
Tapentadol
Pregabalin
Gabapentin
Amitriptyline
Nortriptyline
Duloxetine
Omeprazole
Esomeprazole
Rabeprazole
Pantoprazole
Lansoprazole
Dexlansoprazole
Famotidine
Ranitidine
Domperidone
Ondansetron
Metoclopramide
Sucralfate
Magaldrate
Simethicone
Aluminium Hydroxide
Magnesium Hydroxide
Calcium Carbonate
Lactulose
Bisacodyl
Loperamide
Racecadotril
Oral Rehydration Salt
Psyllium Husk
Ispaghula Husk
Sodium Picosulfate
Polyethylene Glycol
Amoxicillin
Amoxicillin + Clavulanic Acid
Ampicillin
Cloxacillin
Flucloxacillin
Azithromycin
Clarithromycin
Erythromycin
Cefixime
Cefuroxime
Cefpodoxime
Ceftriaxone
Cefotaxime
Cefepime
Cephalexin
Cefradine
Cefaclor
Ceftazidime
Meropenem
Imipenem
Ciprofloxacin
Levofloxacin
Moxifloxacin
Ofloxacin
Norfloxacin
Metronidazole
Secnidazole
Tinidazole
Doxycycline
Tetracycline
Clindamycin
Gentamicin
Amikacin
Linezolid
Vancomycin
Colistin
Rifampicin
Isoniazid
Ethambutol
Pyrazinamide
Fluconazole
Itraconazole
Voriconazole
Terbinafine
Griseofulvin
Clotrimazole
Miconazole
Ketoconazole
Econazole
Nystatin
Acyclovir
Valacyclovir
Tenofovir
Entecavir
Lamivudine
Oseltamivir
Cetirizine
Levocetirizine
Loratadine
Desloratadine
Fexofenadine
Chlorpheniramine
Diphenhydramine
Hydroxyzine
Rupatadine
Bilastine
Montelukast
Salbutamol
Levosalbutamol
Budesonide
Formoterol
Salmeterol
Fluticasone
Beclomethasone
Tiotropium
Ipratropium Bromide
Theophylline
Doxofylline
Ambroxol
Bromhexine
Guaifenesin
Dextromethorphan
Codeine
Pholcodine
Acetylcysteine
Amlodipine
Nifedipine
Cilnidipine
Atenolol
Bisoprolol
Metoprolol
Carvedilol
Propranolol
Losartan
Olmesartan
Telmisartan
Valsartan
Irbesartan
Enalapril
Ramipril
Captopril
Lisinopril
Perindopril
Hydrochlorothiazide
Furosemide
Torasemide
Spironolactone
Indapamide
Chlorthalidone
Atorvastatin
Rosuvastatin
Simvastatin
Pravastatin
Fenofibrate
Gemfibrozil
Ezetimibe
Nitroglycerin
Isosorbide Mononitrate
Isosorbide Dinitrate
Clopidogrel
Prasugrel
Ticagrelor
Warfarin
Rivaroxaban
Apixaban
Dabigatran
Enoxaparin Sodium
Heparin Sodium
Metformin
Glimepiride
Gliclazide
Glibenclamide
Sitagliptin
Vildagliptin
Linagliptin
Saxagliptin
Empagliflozin
Dapagliflozin
Canagliflozin
Pioglitazone
Acarbose
Insulin Human
Insulin Aspart
Insulin Glargine
Insulin Lispro
Insulin Detemir
Semaglutide
Liraglutide
Levothyroxine
Carbimazole
Propylthiouracil
Prednisolone
Dexamethasone
Betamethasone
Hydrocortisone
Methylprednisolone
Deflazacort
Diazepam
Clonazepam
Alprazolam
Lorazepam
Midazolam
Fluoxetine
Sertraline
Escitalopram
Paroxetine
Fluvoxamine
Venlafaxine
Mirtazapine
Quetiapine
Risperidone
Olanzapine
Aripiprazole
Haloperidol
Chlorpromazine
Levetiracetam
Sodium Valproate
Carbamazepine
Phenytoin
Lamotrigine
Topiramate
Phenobarbital
Mecobalamin
Cyanocobalamin
Folic Acid
Ferrous Sulphate
Ferrous Fumarate
Carbonyl Iron
Calcium
Calcium Carbonate + Vitamin D3
Cholecalciferol
Calcitriol
Vitamin B Complex
Multivitamin
Zinc
Magnesium
Potassium Chloride
Vitamin C
Vitamin E
Silymarin
Ursodeoxycholic Acid
Tamsulosin
Finasteride
Dutasteride
Silodosin
Sildenafil
Tadalafil
Vardenafil
Nitrofurantoin
Phenazopyridine
Norethisterone
Dydrogesterone
Progesterone
Levonorgestrel
Ethinylestradiol
Clomiphene Citrate
Letrozole
Cabergoline
Bromocriptine
Metronidazole Vaginal
Clotrimazole Vaginal
Fluconazole Vaginal
Mometasone
Clobetasol
Fluocinolone
Fusidic Acid
Mupirocin
Silver Sulfadiazine
Permethrin
Povidone Iodine
Chlorhexidine
Cetrimide
Benzyl Benzoate
Calamine
Tretinoin
Adapalene
Benzoyl Peroxide
Hydroquinone
Azelaic Acid
Salicylic Acid
Olopatadine
Ketotifen
Carboxymethylcellulose
Hypromellose
Timolol
Brimonidine
Travoprost
Latanoprost
Dorzolamide
Ciprofloxacin Ophthalmic
Moxifloxacin Ophthalmic
Tobramycin
Dexamethasone Ophthalmic
Nepafenac
Sodium Hyaluronate
Xylometazoline
Oxymetazoline
Sodium Chloride Nasal
Fluticasone Nasal
Mometasone Nasal
Azelastine
Betahistine
Cinnarizine
Dimenhydrinate
Meclizine
Domperidone + Omeprazole
Domperidone + Esomeprazole
Levosulpiride
Itopride
Mosapride
Hyoscine Butylbromide
Drotaverine
Mebeverine
Alverine Citrate
Ursodiol
Pancreatin
Rifaximin
Mesalazine
Lactase
Saccharomyces Boulardii
Probiotic Combination
Zinc Sulphate
Zinc Gluconate
Artemether + Lumefantrine
Chloroquine
Hydroxychloroquine
Quinine
Primaquine
Albendazole
Mebendazole
Ivermectin
Praziquantel
Pyrantel Pamoate
Diethylcarbamazine
Allopurinol
Febuxostat
Colchicine
Alendronic Acid
Ibandronic Acid
Risedronate
Glucosamine
Chondroitin
Diacerein
Methotrexate
Leflunomide
Sulfasalazine
Hydroxyurea
Azathioprine
Cyclosporine
Tacrolimus
Mycophenolate Mofetil
Tranexamic Acid
Etamsylate
Vitamin K
Desmopressin
Epoetin Alfa
Iron Sucrose
Ferric Carboxymaltose
Albumin Human
Amino Acid
Dextrose
Sodium Chloride
Hartmann Solution
Ringer Lactate
Mannitol
Potassium Citrate
Sodium Bicarbonate
Acetazolamide
Digoxin
Amiodarone
Verapamil
Diltiazem
Ivabradine
Ranolazine
Nicorandil
Hydralazine
Methyldopa
Prazosin
Terazosin
Minoxidil
Sacubitril + Valsartan
Sevelamer
Calcium Acetate
Cinacalcet
Sodium Polystyrene Sulfonate
Tolfenamic Acid
Rizatriptan
Sumatriptan
Flunarizine
Piracetam
Citicoline
Donepezil
Memantine
Levodopa + Carbidopa
Pramipexole
Ropinirole
Rasagiline
Baclofen
Tizanidine
Orphenadrine
Cyclobenzaprine
Chlorzoxazone
Tolperisone
Prochlorperazine
Trihexyphenidyl
Biperiden
Lithium Carbonate
Buspirone
Zolpidem
Eszopiclone
Melatonin
Atomoxetine
Methylphenidate
Isotretinoin
Minoxidil Topical
Eflornithine
Luliconazole
Sertaconazole
Amorolfine
Ciclopirox
Selenium Sulfide
Coal Tar
Urea
Lactic Acid
Glycolic Acid
Kojic Acid
Niacinamide
Dexpanthenol
Zinc Oxide
Lidocaine
Prilocaine
Bupivacaine
Adrenaline
Atropine
Hyoscine Hydrobromide
Neostigmine
Pyridostigmine
Ondansetron + Pantoprazole
Granisetron
Aprepitant
Megestrol Acetate
Cyproheptadine
Carnitine
Coenzyme Q10
Omega-3 Fatty Acid
Evening Primrose Oil
Ginkgo Biloba
Ginseng
Cranberry Extract
Lactobacillus
Bifidobacterium
Collagen Peptide
Biotin
Lutein
Lycopene
Taurine
Inositol
Myo-Inositol
Alpha Lipoic Acid
Glutathione
Melasma Cream Combination
Ferrous Ascorbate
Calcium Citrate
Magnesium Oxide
Potassium Iodide
Sodium Valproate + Valproic Acid
Clobazam
Oxcarbazepine
Lacosamide
Zonisamide
Vigabatrin
Cefoperazone + Sulbactam
Piperacillin + Tazobactam
Ampicillin + Sulbactam
Meropenem + Vaborbactam
Tigecycline
Fosfomycin
Nitrofurazone
Chloramphenicol
Aztreonam
Polymyxin B
Posaconazole
Amphotericin B
Micafungin
Anidulafungin
Foscarnet
Ganciclovir
Sofosbuvir
Daclatasvir
Ribavirin
Dolutegravir
Efavirenz
Emtricitabine
Zidovudine
Nevirapine
NAMES));

        foreach ($names as $name) {
            GenericName::firstOrCreate(['name' => trim($name)], ['is_active' => true]);
        }
    }
}
