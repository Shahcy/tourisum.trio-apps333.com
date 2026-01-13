<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\Account;
use App\Models\CostCenter;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'المحاسبة';
    protected static ?string $navigationLabel = 'القيود اليومية';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات القيد')
                ->schema([
                    Forms\Components\TextInput::make('entry_no')
                        ->label('رقم القيد')
                        ->required()
                        ->maxLength(50),

                    Forms\Components\DatePicker::make('date')
                        ->label('التاريخ')
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->label('الوصف')
                        ->rows(3)
                        ->nullable(),

                    Forms\Components\Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'draft' => 'مسودة',
                            'posted' => 'مُرحّل',
                        ])
                        ->disabled(),
                ])->columns(2),

            Forms\Components\Section::make('سطور القيد')
                ->schema([
                    Forms\Components\Repeater::make('lines')
                        ->label('السطور')
                        ->relationship()
                        ->minItems(2)
                        ->schema([
                            Forms\Components\Select::make('account_id')
                                ->label('الحساب')
                                ->options(Account::query()->orderBy('code')->pluck('name', 'id'))
                                ->searchable()
                                ->required(),

                            Forms\Components\Select::make('cost_center_id')
                                ->label('مركز التكلفة (اختياري)')
                                ->options(CostCenter::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->nullable(),

                            Forms\Components\TextInput::make('debit')
                                ->label('مدين')
                                ->numeric()
                                ->default(0)
                                ->required(),

                            Forms\Components\TextInput::make('credit')
                                ->label('دائن')
                                ->numeric()
                                ->default(0)
                                ->required(),

                            Forms\Components\TextInput::make('memo')
                                ->label('ملاحظة')
                                ->maxLength(255)
                                ->nullable(),
                        ])
                        ->columns(5),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entry_no')->label('رقم القيد')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date')->label('التاريخ')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => $state === 'posted' ? 'مُرحّل' : 'مسودة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('posted_at')->label('تاريخ الترحيل')->dateTime()->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('post')
                    ->label('ترحيل')
                    ->requiresConfirmation()
                    ->visible(fn(JournalEntry $record) => $record->status === 'draft')
                    ->action(function (JournalEntry $record) {
                        $record->post(Auth::id());
                    }),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
