<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Master';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return App::getLocale() === 'id' ? 'Produk' : 'Product';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name Product'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name')
                    ->preload()
                    ->required()
                    ->native(false)
                    ->searchable(),
                Forms\Components\TextInput::make('price')
                    ->label(__('Price'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                Forms\Components\Toggle::make('is_best_seller')
                    ->label('Favorit?')
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->label(__('Image'))
                    ->getUploadedFileNameForStorageUsing(
                        fn(TemporaryUploadedFile $file): string => (string) time() . '.' . $file->getClientOriginalExtension()
                    )
                    ->image()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25, 50, 100, 250, 500])
            // ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name Product'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->searchable()
                    ->label(__('Price'))
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_best_seller')
                    ->label('Favorit'),
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('Image')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable()
                    ->native(false)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            // 'create' => Pages\CreateProduct::route('/create'),
            // 'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
