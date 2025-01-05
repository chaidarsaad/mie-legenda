<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Lainnya';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return App::getLocale() === 'id' ? 'Pengguna' : 'Users';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('Phone'))
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        $user = Auth::user();

                        // Mengecek apakah user yang login memiliki role 'super_admin'
                        if ($user->hasRole('super_admin')) {
                            // Jika ya, tampilkan semua role
                            return Role::pluck('name', 'id');
                        } else {
                            // Jika bukan super_admin, tampilkan semua role kecuali super_admin
                            return Role::where('name', '!=', 'super_admin')->pluck('name', 'id');
                        }
                    })
                    ->required(),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('Email Verified At')),
                Forms\Components\TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                    ->dehydrated(fn(?string $state): bool => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->minLength(8),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                // Mengecek apakah user yang login memiliki role 'super_admin'
                if (!$user->hasRole('super_admin')) {
                    // Jika bukan super_admin, hanya tampilkan user yang tidak memiliki role super_admin
                    $query->whereDoesntHave('roles', function (Builder $query) {
                        $query->where('name', 'super_admin');
                    });
                }

                // Jika user memiliki super_admin, tidak perlu filter apapun
                return $query;
            })
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25, 50, 100, 250, 500])
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->searchable()
                    ->sortable()
                    ->label(__('Roles')),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('Email Verified At'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
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
                //
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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            // Jika pengguna adalah super_admin, hitung semua pengguna
            $count = User::count();
        } else {
            // Jika pengguna bukan super_admin, hitung pengguna yang tidak memiliki role 'super_admin'
            $count = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super_admin');
            })->count();
        }

        // Kembalikan jumlah yang dihitung
        return $count;
    }
}
