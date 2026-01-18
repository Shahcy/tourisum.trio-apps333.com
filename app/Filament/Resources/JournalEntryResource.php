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
use App\Filament\Resources\Concerns\ScopesToTenant;

class JournalEntryResource extends Resource
{
    use ScopesToTenant;
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('accounting.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('accounting.journal_entries.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('accounting.journal_entries.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('accounting.journal_entries.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('accounting.journal_entries.sections.entry_data'))
                ->schema([
                    Forms\Components\TextInput::make('entry_no')
                        ->label(__('accounting.journal_entries.fields.entry_no'))
                        ->required()
                        ->maxLength(50),

                    Forms\Components\DatePicker::make('date')
                        ->label(__('accounting.journal_entries.fields.date'))
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->label(__('accounting.journal_entries.fields.description'))
                        ->rows(3)
                        ->nullable(),

                    Forms\Components\Select::make('status')
                        ->label(__('accounting.journal_entries.fields.status'))
                        ->options([
                            'draft'  => __('accounting.journal_entries.statuses.draft'),
                            'posted' => __('accounting.journal_entries.statuses.posted'),
                        ])
                        ->disabled(),
                ])->columns(2),

            Forms\Components\Section::make(__('accounting.journal_entries.sections.entry_lines'))
                ->schema([
                    Forms\Components\Repeater::make('lines')
                        ->label(__('accounting.journal_entries.fields.lines'))
                        ->relationship()
                        ->minItems(2)
                        ->schema([
                            Forms\Components\Select::make('account_id')
                                ->label(__('accounting.journal_entries.lines.account'))
                                ->options(Account::query()->orderBy('code')->pluck('name', 'id'))
                                ->searchable()
                                ->required(),

                            Forms\Components\Select::make('cost_center_id')
                                ->label(__('accounting.journal_entries.lines.cost_center'))
                                ->options(CostCenter::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->nullable(),

                            Forms\Components\TextInput::make('debit')
                                ->label(__('accounting.journal_entries.lines.debit'))
                                ->numeric()
                                ->default(0)
                                ->required(),

                            Forms\Components\TextInput::make('credit')
                                ->label(__('accounting.journal_entries.lines.credit'))
                                ->numeric()
                                ->default(0)
                                ->required(),

                            Forms\Components\TextInput::make('memo')
                                ->label(__('accounting.journal_entries.lines.memo'))
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
                Tables\Columns\TextColumn::make('entry_no')
                    ->label(__('accounting.journal_entries.fields.entry_no'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(__('accounting.journal_entries.fields.date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('accounting.journal_entries.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'posted' => __('accounting.journal_entries.statuses.posted'),
                        'draft'  => __('accounting.journal_entries.statuses.draft'),
                        default  => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('posted_at')
                    ->label(__('accounting.journal_entries.fields.posted_at'))
                    ->dateTime()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('post')
                    ->label(__('accounting.journal_entries.actions.post'))
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
