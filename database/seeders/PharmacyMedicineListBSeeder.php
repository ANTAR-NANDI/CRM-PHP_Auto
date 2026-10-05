<?php

namespace Database\Seeders;

use App\Models\GenericName;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PharmacyMedicineListBSeeder extends Seeder
{
    public function run(): void
    {
        $rows = preg_split('/\R/', trim(<<<'DATA'
B Easy|Montelukast Sodium|10 mg|Tablet|Gaco Pharmaceuticals Ltd.
B Easy|Montelukast Sodium|5 mg|Tablet|Gaco Pharmaceuticals Ltd.
B stop|Tranexamic Acid|500 mg|Tablet|Salton Pharmaceuticals Ltd.
B-50 Forte|Vitamin B Complex|5 mg + 2 mg + 2 mg + 20 mg|Tablet|Square Pharmaceuticals PLC
B-50 Forte|Vitamin B Complex|50 mg + 5.48 mg + 100 mg + 5 mg + 10 mg/2 ml|Injection|Square Pharmaceuticals PLC
B-50 Forte|Vitamin B Complex|5 mg + 2 mg + 2 mg + 20 mg/5 ml|Syrup|Square Pharmaceuticals PLC
B-9|Folic Acid|2.5 mg/5 ml|Syrup|Square Pharmaceuticals PLC
B-Card|Atenolol|50 mg|Tablet|Nipa Pharmaceuticals Ltd.
B-Cef|Cetirizine Hydrochloride|10 mg|Tablet|Belsen Pharmaceuticals Ltd.
B-Cef|Cetirizine Hydrochloride|5 mg/5 ml|Syrup|Belsen Pharmaceuticals Ltd.
B-Cort|Betamethasone Sodium Phosphate|0.1%|Eye/Ear Drop|Globe Pharmaceuticals Ltd.
B-Dexa|Dexamethasone|0.5 mg|Tablet|Belsen Pharmaceuticals Ltd.
B-Mycin|Neomycin + Bacitracin Zinc|5 mg + 500 IU/gm|Ointment|Gaco Pharmaceuticals Ltd.
B-One|Thiamine Hydrochloride|100 mg|Tablet|Alco Pharma Ltd.
B-Plex|Vitamin B Complex|5 mg + 2 mg + 2 mg + 20 mg|Tablet|Ad-din Pharmaceuticals Ltd.
B-Plex|Vitamin B Complex|5 mg + 2 mg + 2 mg + 20 mg/5 ml|Syrup|Ad-din Pharmaceuticals Ltd.
B-Z|Vitamin B Complex + Zinc|N/A|Tablet/Syrup|Ad-din Pharmaceuticals Ltd.
B-Zn|Zinc Sulfate Monohydrate|10 mg/5 ml|Syrup|Benham Pharmaceuticals Ltd.
B126|Vitamin B1 + Vitamin B6 + Vitamin B12|100 mg + 200 mg + 200 mcg|Tablet|Popular Pharmaceuticals Ltd.
B3|Vitamin B1 + Vitamin B6 + Vitamin B12|100 mg + 200 mg + 200 mcg|Tablet|Al-Madina Pharmaceuticals Ltd.
Babiz|Vitamin B Complex + Zinc|N/A|Syrup|Rangs Pharmaceuticals Ltd.
Baby saline|Sodium Chloride + Dextrose|0.225% + 5%|IV Infusion|Libra Infusions Ltd.
Baby Zinc|Zinc Sulfate Monohydrate|20 mg|Tablet/Syrup|ACME Laboratories Ltd.
Babykion|Phytomenadione|2 mg/0.2 ml|Injection|Chemist Laboratories Ltd.
Babysol|Sodium Chloride + Dextrose|0.225% + 5%|IV Infusion|OSL Pharma Limited
Babysol Junior|Sodium Chloride + Dextrose|0.45% + 5%|IV Infusion|OSL Pharma Limited
Bacaid|Baclofen|10 mg|Tablet|Labaid Pharma Ltd.
Bacaid|Baclofen|5 mg|Tablet|Labaid Pharma Ltd.
Bacben|Baclofen|10 mg|Tablet|Benham Pharmaceuticals Ltd.
Bacifen|Baclofen|10 mg|Tablet|Biopharma Limited
Bacilex|Pivmecillinam|200 mg|Tablet|Pharmadesh Laboratories Ltd.
Backtone|Baclofen|10 mg|Tablet|Pharmacil Limited
Backtone|Baclofen|5 mg|Tablet|Pharmacil Limited
Baclax|Baclofen|10 mg|Tablet|Silco Pharmaceutical Ltd.
Baclium|Baclofen|10 mg|Tablet|Virgo Pharmaceuticals Ltd.
Baclobac|Baclofen|5 mg|Tablet|Pharmasia Limited
Baclobac|Baclofen|10 mg|Tablet|Pharmasia Limited
Baclodol|Baclofen|10 mg|Tablet|Astra Biopharmaceuticals Ltd.
Baclof|Baclofen|10 mg|Tablet|Pacific Pharmaceuticals Ltd.
Baclof|Baclofen|5 mg|Tablet|Pacific Pharmaceuticals Ltd.
Baclofen|Baclofen|10 mg|Tablet|Amico Laboratories Ltd.
Bacloflex|Baclofen|10 mg|Tablet|Somatec Pharmaceuticals Ltd.
Baclomark|Baclofen|10 mg|Tablet|Hallmark Pharmaceuticals Ltd.
Baclomax|Baclofen|10 mg|Tablet|Veritas Pharmaceuticals Ltd.
Baclon|Baclofen|5 mg|Tablet|Orion Pharma Ltd.
Baclon|Baclofen|10 mg|Tablet|Orion Pharma Ltd.
Baclosic|Baclofen|10 mg|Tablet|Physic Pharmaceuticals Ltd.
Bacmax|Baclofen|5 mg|Tablet|Drug International Ltd.
Bacmax|Baclofen|10 mg|Tablet|Drug International Ltd.
Bacnil|Levofloxacin Hemihydrate|500 mg|Tablet|Rephco Pharmaceuticals Ltd.
Bacofen|Baclofen|5 mg|Tablet|The IBN SINA Pharmaceutical PLC
Bacofen|Baclofen|10 mg|Tablet|The IBN SINA Pharmaceutical PLC
Bacron|Mupirocin|2% w/w|Ointment|Biopharma Limited
Bacspa|Baclofen|10 mg|Tablet|Apex Pharma Ltd.
Bactab|Baclofen|10 mg|Tablet|Rangs Pharmaceuticals Ltd.
Bactacef|Cephradine|500 mg|Capsule|Silco Pharmaceutical Ltd.
Bactacef|Cephradine|125 mg/5 ml|Suspension|Silco Pharmaceutical Ltd.
Bactacef DS|Cephradine|250 mg/5 ml|Suspension|Silco Pharmaceutical Ltd.
Bactamox|Amoxicillin Trihydrate|250 mg|Capsule|Renata PLC
Bactamox|Amoxicillin Trihydrate|500 mg|Capsule|Renata PLC
Bactamox|Amoxicillin Trihydrate|125 mg/5 ml|Suspension|Renata PLC
Bactamox|Amoxicillin Trihydrate|125 mg/1.25 ml|Paediatric Drop|Renata PLC
Bactazim|Cefixime Trihydrate|200 mg|Tablet/Capsule|Silco Pharmaceutical Ltd.
Bactazim|Cefixime Trihydrate|400 mg|Tablet/Capsule|Silco Pharmaceutical Ltd.
Bactazim|Cefixime Trihydrate|100 mg/5 ml|Suspension|Silco Pharmaceutical Ltd.
Bactikil|Cefuroxime Axetil|250 mg|Tablet|Edruc Limited
Bactikil|Cefuroxime Axetil|500 mg|Tablet|Edruc Limited
Bactikil|Cefuroxime Axetil|125 mg/5 ml|Suspension|Edruc Limited
Bactin|Ciprofloxacin|0.3%|Eye Drop|The IBN SINA Pharmaceutical PLC
Bactin|Ciprofloxacin|250 mg|Tablet|The IBN SINA Pharmaceutical PLC
Bactin|Ciprofloxacin|500 mg|Tablet|The IBN SINA Pharmaceutical PLC
Bactin|Ciprofloxacin|750 mg|Tablet|The IBN SINA Pharmaceutical PLC
Bactin|Ciprofloxacin|200 mg/100 ml|IV Infusion|The IBN SINA Pharmaceutical PLC
Bactin|Ciprofloxacin|250 mg/5 ml|Suspension|The IBN SINA Pharmaceutical PLC
Bactin D|Ciprofloxacin + Dexamethasone|0.3% + 0.1%|Eye/Ear Drop|The IBN SINA Pharmaceutical PLC
Bactin HC|Ciprofloxacin + Hydrocortisone|0.3% + 1%|Eye/Ear Drop|The IBN SINA Pharmaceutical PLC
Bactoderm|Mupirocin|2% w/w|Ointment|UniMed UniHealth Pharmaceuticals Ltd.
Bactokil|Cephradine|250 mg|Capsule|Virgo Pharmaceuticals Ltd.
Bactokil|Cephradine|500 mg|Capsule|Virgo Pharmaceuticals Ltd.
Bactokil|Cephradine|125 mg/5 ml|Suspension|Virgo Pharmaceuticals Ltd.
Bactolev|Levofloxacin|1.5%|Eye/Ear Drop|UNIDO Pharmaceuticals Ltd.
Bactovate|Betamethasone + Neomycin|0.1% + 0.5%|Cream/Ointment|Jenphar Bangladesh Ltd.
Bactover|Mupirocin|2% w/w|Ointment|Beacon Pharmaceuticals PLC
Bactriben|Mupirocin|2% w/w|Ointment|DBL Pharmaceuticals Ltd.
Bactrobex|Mupirocin|2% w/w|Ointment|Beximco Pharmaceuticals Ltd.
Bactrocin|Mupirocin|2% w/w|Ointment|Square Pharmaceuticals PLC
Bactropen|Mupirocin|2% w/w|Ointment|NIPRO JMI Pharma Ltd.
Bakticef|Ceftriaxone Sodium|500 mg/vial|Injection|OSL Pharma Limited
Bakticef|Ceftriaxone Sodium|1 gm/vial|Injection|OSL Pharma Limited
Bakticef|Ceftriaxone Sodium|2 gm/vial|Injection|OSL Pharma Limited
Balancia|Citicoline Sodium|500 mg|Tablet|Aristopharma Ltd.
Balo|Baclofen|10 mg|Tablet|Medicon Pharmaceuticals Ltd.
Balovir|Baloxavir Marboxil|40 mg|Tablet|ACME Laboratories Ltd.
Baloxa|Baloxavir Marboxil|20 mg|Tablet|Beximco Pharmaceuticals Ltd.
Baloxa|Baloxavir Marboxil|40 mg|Tablet|Beximco Pharmaceuticals Ltd.
Bangay|Methyl Salicylate + Menthol|30% + 8%|Cream/Ointment|Sharif Pharmaceuticals Ltd.
Bangay Ultra|Methyl Salicylate + Menthol + Camphor|30% + 10% + 4%|Cream/Ointment|Sharif Pharmaceuticals Ltd.
Bantovet|Betamethasone Valerate|0.1%|Cream/Ointment|DBL Pharmaceuticals Ltd.
Bantovet-CL|Betamethasone + Clotrimazole|0.1% + 1%|Cream/Ointment|DBL Pharmaceuticals Ltd.
Banxyt|Flupentixol + Melitracen|0.5 mg + 10 mg|Tablet|Bristol Pharmaceuticals Ltd.
DATA));

        foreach ($rows as $index => $row) {
            [$brand, $genericName, $strength, $form, $supplierName] = array_map('trim', explode('|', $row));
            $generic = GenericName::firstOrCreate(['name' => $genericName], ['is_active' => true]);
            $brandRecord = Brand::firstOrCreate(['name' => $brand], ['is_active' => true]);
            $supplier = Supplier::firstOrCreate(['name' => $supplierName], ['is_active' => true]);
            $category = $this->categoryFor($form);
            Product::updateOrCreate(['name' => "{$brand} {$strength} {$form}", 'generic_name_id' => $generic->id], ['barcode' => 'LSTB'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT), 'brand_id' => $brandRecord->id, 'default_supplier_id' => $supplier->id, 'category' => $category, 'unit' => in_array($category, ['suspension', 'syrup'], true) ? 'bottle' : 'piece', 'pieces_per_strip' => in_array($category, ['tablet', 'capsule'], true) ? 10 : 1, 'sell_by_piece' => true, 'sell_by_strip' => in_array($category, ['tablet', 'capsule'], true), 'reorder_level' => 10, 'is_active' => true]);
        }
    }

    private function categoryFor(string $form): string
    {
        $form = strtolower($form);
        return match (true) {
            str_contains($form, 'injection') || str_contains($form, 'infusion') => 'injection', str_contains($form, 'suspension') => 'suspension', str_contains($form, 'syrup') => 'syrup', str_contains($form, 'drop') => 'drops', str_contains($form, 'ointment') => 'ointment', str_contains($form, 'cream') => 'cream', str_contains($form, 'capsule') => 'capsule', default => 'tablet',
        };
    }
}
