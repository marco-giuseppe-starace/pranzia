<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Http\Requests\UploadMenuItemImageRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MenuItemResource::collection(
            MenuItem::with('allergens')->orderBy('name')->get()
        );
    }

    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $menuItem = MenuItem::create($request->safe()->except('allergen_ids'));
        $menuItem->allergens()->sync($request->input('allergen_ids', []));

        // fresh() rilegge dal DB i valori di default (es. "available")
        // che il modello in memoria non conosce dopo la sola create().
        return MenuItemResource::make($menuItem->fresh('allergens'))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): JsonResponse
    {
        $menuItem->update($request->safe()->except('allergen_ids'));

        if ($request->has('allergen_ids')) {
            $menuItem->allergens()->sync($request->input('allergen_ids'));
        }

        return MenuItemResource::make($menuItem->load('allergens'))->response();
    }

    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $this->deleteImageFile($menuItem);
        $menuItem->delete();

        return response()->json(status: 204);
    }

    // URL relativo (non Storage::url(), che prefissa APP_URL): cosi' il
    // frontend lo risolve sempre rispetto alla propria origin, passando dal
    // proxy /storage di Vite in sviluppo (vedi vite.config.js), qualunque
    // sia l'host con cui e' stata caricata la pagina.
    public function uploadImage(UploadMenuItemImageRequest $request, MenuItem $menuItem): JsonResponse
    {
        $this->deleteImageFile($menuItem);

        $path = $request->file('image')->store('menu-items', 'public');
        $this->resizeImage(Storage::disk('public')->path($path));
        $menuItem->update(['image_url' => '/storage/'.$path]);

        return MenuItemResource::make($menuItem->fresh('allergens'))->response();
    }

    private function deleteImageFile(MenuItem $menuItem): void
    {
        if ($menuItem->image_url) {
            Storage::disk('public')->delete(Str::after($menuItem->image_url, '/storage/'));
        }
    }

    // Le foto scattate con lo smartphone arrivano spesso a 4000-5000px di
    // lato e diversi MB: sul menu vengono mostrate in card piccole, quindi
    // le ridimensioniamo qui una volta sola invece di far scaricare al
    // cliente (spesso da rete mobile al tavolo) l'originale a piena
    // risoluzione ogni volta che apre il menu.
    private function resizeImage(string $absolutePath, int $maxDimension = 1600): void
    {
        $info = @getimagesize($absolutePath);
        if (! $info) {
            return;
        }

        [$width, $height, $type] = $info;

        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($absolutePath),
            IMAGETYPE_PNG => imagecreatefrompng($absolutePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($absolutePath),
            default => null,
        };
        if (! $source) {
            return;
        }

        // I pixel di una foto da smartphone spesso non sono ruotati:
        // l'orientamento "corretto" sta solo nel tag EXIF, che i browser
        // rispettano in visualizzazione ma che GD scarta al salvataggio.
        // Va applicato qui, altrimenti la versione ridimensionata risulta
        // storta.
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $orientation = (@exif_read_data($absolutePath))['Orientation'] ?? 1;
            $source = match ($orientation) {
                3 => imagerotate($source, 180, 0),
                6 => imagerotate($source, -90, 0),
                8 => imagerotate($source, 90, 0),
                default => $source,
            };
            $width = imagesx($source);
            $height = imagesy($source);
        }

        // Molte foto prodotto (es. bottiglie su sfondo bianco) hanno un
        // grosso margine vuoto attorno al soggetto: rifilandolo, il
        // soggetto riempie molto meglio la miniatura piccola e quadrata
        // usata dalle card.
        [$cropX, $cropY, $cropWidth, $cropHeight] = $this->trimUniformBorder($source, $width, $height);
        if ($cropWidth < $width || $cropHeight < $height) {
            $cropped = imagecrop($source, ['x' => $cropX, 'y' => $cropY, 'width' => $cropWidth, 'height' => $cropHeight]);
            if ($cropped !== false) {
                imagedestroy($source);
                $source = $cropped;
                $width = $cropWidth;
                $height = $cropHeight;
            }
        }

        $scale = min($maxDimension / $width, $maxDimension / $height, 1);
        $newWidth = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($resized, $absolutePath, 82),
            IMAGETYPE_PNG => imagepng($resized, $absolutePath, 6),
            IMAGETYPE_WEBP => imagewebp($resized, $absolutePath, 82),
            default => null,
        };

        imagedestroy($source);
        imagedestroy($resized);
    }

    // Rileva un bordo di colore quasi uniforme (tipico sfondo bianco/neutro
    // delle foto prodotto) e restituisce il rettangolo di ritaglio che lo
    // esclude. Campiona i pixel a intervalli (non uno per uno: le foto
    // arrivano anche a 5000px di lato) e non rifila mai piu' del 35% per
    // lato, cosi' una foto senza sfondo uniforme (es. un piatto a schermo
    // pieno) resta semplicemente intatta invece di rischiare un ritaglio
    // scorretto.
    private function trimUniformBorder($image, int $width, int $height): array
    {
        $tolerance = 14;
        $maxTrimRatio = 0.35;
        $step = max(1, (int) ($width / 200));

        $corners = [
            imagecolorat($image, 0, 0),
            imagecolorat($image, $width - 1, 0),
            imagecolorat($image, 0, $height - 1),
            imagecolorat($image, $width - 1, $height - 1),
        ];
        $bg = [0, 0, 0];
        foreach ($corners as $c) {
            $bg[0] += ($c >> 16) & 0xFF;
            $bg[1] += ($c >> 8) & 0xFF;
            $bg[2] += $c & 0xFF;
        }
        $bg = array_map(fn ($v) => (int) round($v / 4), $bg);

        $isBackground = function (int $x, int $y) use ($image, $bg, $tolerance): bool {
            $c = imagecolorat($image, $x, $y);
            return abs((($c >> 16) & 0xFF) - $bg[0]) <= $tolerance
                && abs((($c >> 8) & 0xFF) - $bg[1]) <= $tolerance
                && abs(($c & 0xFF) - $bg[2]) <= $tolerance;
        };

        $rowIsBackground = function (int $y) use ($width, $step, $isBackground): bool {
            for ($x = 0; $x < $width; $x += $step) {
                if (! $isBackground($x, $y)) {
                    return false;
                }
            }
            return true;
        };
        $colIsBackground = function (int $x) use ($height, $step, $isBackground): bool {
            for ($y = 0; $y < $height; $y += $step) {
                if (! $isBackground($x, $y)) {
                    return false;
                }
            }
            return true;
        };

        $maxTrimX = (int) ($width * $maxTrimRatio);
        $maxTrimY = (int) ($height * $maxTrimRatio);

        $top = 0;
        for ($y = 0; $y < $maxTrimY && $rowIsBackground($y); $y += $step) {
            $top = $y + $step;
        }
        $bottom = $height - 1;
        for ($y = $height - 1; $y > $height - 1 - $maxTrimY && $rowIsBackground($y); $y -= $step) {
            $bottom = $y - $step;
        }
        $left = 0;
        for ($x = 0; $x < $maxTrimX && $colIsBackground($x); $x += $step) {
            $left = $x + $step;
        }
        $right = $width - 1;
        for ($x = $width - 1; $x > $width - 1 - $maxTrimX && $colIsBackground($x); $x -= $step) {
            $right = $x - $step;
        }

        $top = min($top, $maxTrimY);
        $left = min($left, $maxTrimX);
        $bottom = max($bottom, $height - 1 - $maxTrimY);
        $right = max($right, $width - 1 - $maxTrimX);

        $cropWidth = $right - $left + 1;
        $cropHeight = $bottom - $top + 1;
        if ($cropWidth < $width * 0.3 || $cropHeight < $height * 0.3 || $cropWidth > $width || $cropHeight > $height) {
            return [0, 0, $width, $height];
        }

        return [$left, $top, $cropWidth, $cropHeight];
    }
}
