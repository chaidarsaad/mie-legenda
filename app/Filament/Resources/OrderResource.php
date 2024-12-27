<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Master';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return App::getLocale() === 'id' ? 'Pesanan' : 'Orders';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('transaction_time')
                    ->required(),
                Forms\Components\TextInput::make('total_price')
                    ->label(__('Total Price'))
                    ->prefix('Rp')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_item')
                    ->label(__('Total Item'))
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('kasir.name')
                    ->label(__('Cashier Name'))
                    ->relationship('kasir', 'name')
                    ->preload()
                    ->required()
                    ->native(false)
                    ->searchable(),
                Forms\Components\TextInput::make('payment_method')
                    ->label(__('Payment Method'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('transaction_time')
                    ->label(__('Transaction Time'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->prefix('Rp ')
                    ->label(__('Total Price'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_item')
                    ->label(__('Total Item'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kasir.name')
                    ->label(__('Cashier Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Payment Method'))
                    ->searchable(),
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
                SelectFilter::make('payment_method')
                    ->label(__('Payment Method'))
                    ->preload()
                    ->searchable()
                    ->native(false)
                    ->options([
                        'Tunai' => ('Tunai'),
                        'qris' => ('QRIS'),
                        'transfer' => ('Transfer'),
                    ]),
                Filter::make('transaction_time')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('Created from'))
                            ->default(Carbon::now()->startOfMonth()->format('Y-m-d 00:00:00')),
                        DatePicker::make('created_until')
                            ->label(__('Created until'))
                            ->default(Carbon::now()->endOfMonth()->format('Y-m-d 23:59:59')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_time', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_time', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            // 'create' => Pages\CreateOrder::route('/create'),
            // 'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
