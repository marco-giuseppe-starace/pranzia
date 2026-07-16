<?php

namespace Database\Seeders;

use App\Enums\MenuCategoryGroup;
use App\Models\Allergen;
use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    // Menu di esempio di un ristorante italiano tipico, con allergeni
    // assegnati coerentemente a ogni piatto (dato verificato manualmente,
    // mai dedotto dall'IA — vedi docs/ia-guardrail.md).
    private const CATEGORIES = [
        'Antipasti' => [
            ['name' => 'Bruschetta al pomodoro', 'description' => 'Pane tostato, pomodoro fresco, basilico e olio EVO.', 'price' => 6.50, 'allergens' => ['Glutine']],
            ['name' => 'Tagliere di salumi e formaggi', 'description' => 'Selezione di salumi e formaggi locali.', 'price' => 12.00, 'allergens' => ['Latte', 'Solfiti']],
            ['name' => 'Carpaccio di manzo', 'description' => 'Manzo crudo battuto a coltello, rucola e grana.', 'price' => 11.00, 'allergens' => ['Latte']],
            ['name' => 'Insalata di mare', 'description' => 'Polpo, gamberi e cozze con limone e prezzemolo.', 'price' => 13.50, 'allergens' => ['Crostacei', 'Molluschi', 'Pesce']],
        ],
        'Primi' => [
            ['name' => 'Spaghetti alle vongole', 'description' => 'Vongole veraci, aglio, olio e prezzemolo.', 'price' => 14.00, 'allergens' => ['Glutine', 'Molluschi', 'Solfiti']],
            ['name' => 'Risotto ai funghi porcini', 'description' => 'Riso carnaroli mantecato al burro e parmigiano.', 'price' => 13.00, 'allergens' => ['Latte']],
            ['name' => 'Lasagna alla bolognese', 'description' => 'Sfoglia fresca, ragu' . "'" . ' e besciamella.', 'price' => 12.50, 'allergens' => ['Glutine', 'Latte', 'Uova']],
            ['name' => 'Orecchiette con cime di rapa', 'description' => 'Ricetta tradizionale pugliese con aglio e acciughe.', 'price' => 11.50, 'allergens' => ['Glutine', 'Pesce']],
        ],
        'Secondi' => [
            ['name' => 'Tagliata di manzo', 'description' => 'Con rucola, grana e riduzione di aceto balsamico.', 'price' => 18.00, 'allergens' => ['Latte']],
            ['name' => 'Branzino al forno', 'description' => 'Con patate, olive taggiasche e pomodorini.', 'price' => 19.00, 'allergens' => ['Pesce']],
            ['name' => 'Cotoletta alla milanese', 'description' => 'Impanata e fritta nel burro chiarificato.', 'price' => 16.00, 'allergens' => ['Glutine', 'Uova', 'Latte']],
            ['name' => 'Parmigiana di melanzane', 'description' => 'Melanzane fritte, mozzarella, pomodoro e basilico.', 'price' => 12.00, 'allergens' => ['Latte', 'Uova']],
        ],
        'Bibite' => [
            ['name' => 'Acqua naturale / frizzante', 'description' => 'Bottiglia 0.75L.', 'price' => 2.00, 'allergens' => []],
            ['name' => 'Coca-Cola / Aranciata', 'description' => 'Lattina 33cl.', 'price' => 3.00, 'allergens' => []],
            ['name' => 'Succo di frutta', 'description' => 'Pesca, ananas o pera, 20cl.', 'price' => 3.00, 'allergens' => []],
        ],
        'Birre' => [
            ['name' => 'Birra artigianale alla spina', 'description' => 'Bionda, produzione locale, 40cl.', 'price' => 5.00, 'allergens' => ['Glutine']],
            ['name' => 'Birra doppio malto in bottiglia', 'description' => 'Ambrata, 33cl.', 'price' => 5.50, 'allergens' => ['Glutine']],
        ],
        'Vini' => [
            ['name' => 'Vino della casa (calice)', 'description' => 'Rosso o bianco, selezione del sommelier.', 'price' => 4.50, 'allergens' => ['Solfiti']],
            ['name' => 'Prosecco (calice)', 'description' => 'Extra dry, bollicine fini.', 'price' => 5.00, 'allergens' => ['Solfiti']],
            ['name' => 'Bottiglia di rosso della casa', 'description' => 'Vino rosso del territorio, 0.75L.', 'price' => 18.00, 'allergens' => ['Solfiti']],
        ],
        'Dolci al cucchiaio' => [
            ['name' => 'Tiramisu' . "'", 'description' => 'Savoiardi, caffe' . "'" . ', mascarpone e cacao.', 'price' => 6.00, 'allergens' => ['Uova', 'Latte', 'Glutine']],
            ['name' => 'Panna cotta', 'description' => 'Con coulis di frutti di bosco.', 'price' => 5.50, 'allergens' => ['Latte']],
            ['name' => 'Cannolo siciliano', 'description' => 'Ripieno di ricotta fresca e gocce di cioccolato.', 'price' => 5.00, 'allergens' => ['Glutine', 'Latte', 'Frutta a guscio']],
        ],
        'Frutta' => [
            ['name' => 'Macedonia di frutta fresca', 'description' => 'Frutta di stagione tagliata al momento.', 'price' => 5.00, 'allergens' => []],
            ['name' => 'Ananas grigliato', 'description' => 'Con una pallina di gelato alla vaniglia.', 'price' => 5.50, 'allergens' => ['Latte']],
            ['name' => 'Frutti di bosco con panna', 'description' => 'Mirtilli, lamponi e more con panna fresca.', 'price' => 6.00, 'allergens' => ['Latte']],
        ],
        'Crostate' => [
            ['name' => 'Crostata alla frutta', 'description' => 'Pasta frolla, crema pasticcera e frutta fresca.', 'price' => 5.50, 'allergens' => ['Glutine', 'Uova', 'Latte']],
            ['name' => 'Crostata di mele', 'description' => 'Pasta frolla e mele caramellate alla cannella.', 'price' => 5.00, 'allergens' => ['Glutine', 'Uova', 'Latte']],
        ],
        'Cioccolato' => [
            ['name' => 'Tortino al cioccolato fondente', 'description' => 'Cuore caldo e morbido, servito con gelato.', 'price' => 6.50, 'allergens' => ['Glutine', 'Uova', 'Latte']],
            ['name' => 'Mousse al cioccolato', 'description' => 'Cioccolato fondente 70%, montata con panna fresca.', 'price' => 5.50, 'allergens' => ['Uova', 'Latte']],
            ['name' => 'Semifreddo al cioccolato e nocciole', 'description' => 'Con granella di nocciole tostate.', 'price' => 6.00, 'allergens' => ['Latte', 'Uova', 'Frutta a guscio']],
        ],
    ];

    // Macro-sezione verticale (cibo/bevande/dolci) di ogni categoria, usata
    // dal frontend per raggruppare il menu in 3 blocchi (vedi MenuView.vue).
    private const CATEGORY_GROUPS = [
        'Antipasti' => MenuCategoryGroup::Food,
        'Primi' => MenuCategoryGroup::Food,
        'Secondi' => MenuCategoryGroup::Food,
        'Bibite' => MenuCategoryGroup::Drink,
        'Birre' => MenuCategoryGroup::Drink,
        'Vini' => MenuCategoryGroup::Drink,
        'Dolci al cucchiaio' => MenuCategoryGroup::Dessert,
        'Frutta' => MenuCategoryGroup::Dessert,
        'Crostate' => MenuCategoryGroup::Dessert,
        'Cioccolato' => MenuCategoryGroup::Dessert,
    ];

    public function run(): void
    {
        $allergensByName = Allergen::all()->keyBy('name');

        $sortOrder = 0;
        foreach (self::CATEGORIES as $categoryName => $items) {
            $category = MenuCategory::firstOrCreate(
                ['name' => $categoryName],
                ['sort_order' => $sortOrder, 'group' => self::CATEGORY_GROUPS[$categoryName]]
            );

            foreach ($items as $item) {
                $menuItem = $category->menuItems()->firstOrCreate(
                    ['name' => $item['name']],
                    [
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'available' => true,
                    ]
                );

                $allergenIds = collect($item['allergens'])
                    ->map(fn (string $name) => $allergensByName->get($name)?->id)
                    ->filter()
                    ->all();

                $menuItem->allergens()->sync($allergenIds);
            }

            $sortOrder++;
        }
    }
}
