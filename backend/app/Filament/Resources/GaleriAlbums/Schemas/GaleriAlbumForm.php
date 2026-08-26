<?php

namespace App\Filament\Resources\GaleriAlbums\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GaleriAlbumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('judul')->required()->maxLength(255),
            Textarea::make('deskripsi')->columnSpanFull(),
            Select::make('komunitas_id')
                ->relationship('komunitas', 'nama')
                ->label('Tampil untuk')
                ->helperText('Kosongkan = galeri himpunan umum'),
            Select::make('event_id')
                ->relationship('event', 'judul')
                ->label('Terkait event (opsional)'),
        ]);
    }
}
