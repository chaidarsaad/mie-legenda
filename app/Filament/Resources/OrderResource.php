<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
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
                Section::make('Order')
                    ->collapsible()
                    ->schema([
                        Forms\Components\DateTimePicker::make('transaction_time')
                            ->required(),
                        Forms\Components\Select::make('kasir.name')
                            ->label(__('Cashier Name'))
                            ->relationship('kasir', 'name')
                            ->preload()
                            ->required()
                            ->native(false)
                            ->searchable(),
                        Forms\Components\TextInput::make('payment_method')
                            ->default('Tunai')
                            ->label(__('Payment Method'))
                            ->required()
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Produk dipesan')
                    ->collapsible()
                    ->schema([
                        self::getItemsRepeater(),
                    ]),
                Forms\Components\Section::make('Harga')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('total_item')
                            ->label(__('Total Item'))
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('total_price')
                            ->label(__('Total Price'))
                            ->prefix('Rp')
                            ->required()
                            ->numeric(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_time', 'desc')
            ->poll('10s')
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25, 50, 100, 250, 500])
            // ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('transaction_time')
                    ->searchable()
                    ->label(__('Transaction Time'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->searchable()
                    ->money('IDR')
                    ->label(__('Total Price'))
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
                        DatePicker::make('pesanan_dari'),
                        DatePicker::make('pesanan_sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['pesanan_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_time', '>=', $date),
                            )
                            ->when(
                                $data['pesanan_sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_time', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['pesanan_dari'] ?? null) {
                            $indicators[] = Indicator::make('Pesanan dari ' . Carbon::parse($data['pesanan_dari'])->toFormattedDateString())
                                ->removeField('pesanan_dari');
                        }

                        if ($data['pesanan_sampai'] ?? null) {
                            $indicators[] = Indicator::make('Pesanan sampai ' . Carbon::parse($data['pesanan_sampai'])->toFormattedDateString())
                                ->removeField('pesanan_sampai');
                        }

                        return $indicators;
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->color('gray')
                    ->label('Export Pesanan')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Dari tanggal')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Sampai tanggal')
                            ->required(),
                    ])
                    ->openUrlInNewTab()
                    ->action(function (array $data) {
                        return redirect()->route('download-data-pesanan', [
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date']
                        ]);
                    })
                    ->visible(function () {
                        return Order::exists();
                    }),
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

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('orders')
            ->relationship()
            ->live()
            ->columns([
                'md' => 10,
            ])
            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                self::updateTotalPrice($get, $set);
            })
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->native(false)
                    ->searchable()
                    ->label('Produk')
                    ->required()
                    ->options(Product::query()->pluck('name', 'id'))
                    ->columnSpan([
                        'md' => 5
                    ])
                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                        $product = Product::find($state);
                        $set('unit_price', $product->price ?? 0);
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $product = Product::find($state);
                        $set('unit_price', $product->price ?? 0);
                        $quantity = $get('quantity') ?? 1;
                        self::updateTotalPrice($get, $set);
                    })
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->preload(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->columnSpan([
                        'md' => 1
                    ])
                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                        self::updateTotalPrice($get, $set);
                    }),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Harga saat ini')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->columnSpan([
                        'md' => 3
                    ]),

            ]);
    }

    protected static function updateTotalPrice(Forms\Get $get, Forms\Set $set): void
    {
        $selectedProducts = collect($get('orders'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        // Menghitung total harga
        $totalPrice = $selectedProducts->reduce(function ($total, $product) use ($prices) {
            return $total + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        // Menghitung total jumlah item
        $totalItem = $selectedProducts->reduce(function ($total, $product) {
            return $total + $product['quantity'];
        }, 0);

        $set('total_price', $totalPrice);
        $set('total_item', $totalItem);
    }
}
