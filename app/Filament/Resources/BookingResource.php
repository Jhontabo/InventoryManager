<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\BookingResource\Pages\ListBookings;
use App\Filament\Resources\BookingResource\Pages\ViewCalendarReadOnly;
use App\Models\AcademicProgram;
use App\Models\Booking;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action as TableAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BookingResource extends Resource
{
    use HasPanelRoleAccess;

    // This resource lists schedule slots and creates Booking records from them.
    protected static ?string $model = Schedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $modelLabel = null;

    protected static ?string $navigationLabel = null;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(['ADMIN', 'COORDINADOR', 'LABORATORISTA', 'DOCENTE', 'ESTUDIANTE']);
    }

    public static function getModelLabel(): string
    {
        return __('panel.booking.resource_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.booking.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.bookings');
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make()
                ->label(__('panel.booking.nav_item'))
                ->url(static::getUrl())
                ->icon(static::$navigationIcon)
                ->group(__('panel.nav.bookings'))
                ->isActiveWhen(fn (): bool => request()->is('admin/bookings') && ! request()->is('admin/bookings/calendario')),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'calendario' => ViewCalendarReadOnly::route('/calendario'),
        ];
    }

    public static function table(Table $table): Table
    {
        $today = Carbon::now()->startOfDay();
        $limit = Carbon::now()->addMonth()->endOfDay();

        return $table
            ->query(
                Schedule::where('type', 'unstructured')
                    ->select(['id', 'laboratory_id', 'start_at', 'end_at', 'type'])
                    ->whereBetween('start_at', [$today, $limit])
                    ->orderBy('start_at')
                    ->with(['laboratory', 'booking' => function ($query) {
                        $query->where('status', 'approved');
                    }])
                    ->withCount(['booking' => function ($query) {
                        $query->where('status', 'approved');
                    }])
            )
            ->columns([
                TextColumn::make('laboratory.name')
                    ->label(__('panel.booking.lab_space'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(
                        fn (Schedule $record): string => $record->booking_count > 0 ? 'gray' : 'success'
                    )
                    ->formatStateUsing(
                        fn (Schedule $record) => $record->laboratory->name.
                          ($record->booking_count > 0 ? ' ('.__('panel.booking.slot_occupied').')' : ' ('.__('panel.booking.slot_available').')')
                    ),

                TextColumn::make('start_at')
                    ->label(__('panel.booking.start'))
                    ->sortable()
                    ->formatStateUsing(
                        fn (string $state): string => Carbon::parse($state)->locale(app()->getLocale())->translatedFormat('l, d \d\e F \d\e Y - g:i A')
                    ),

                TextColumn::make('end_at')
                    ->label(__('panel.booking.end'))
                    ->sortable()
                    ->formatStateUsing(
                        fn (string $state): string => Carbon::parse($state)->locale(app()->getLocale())->translatedFormat('l, d \d\e F \d\e Y - g:i A')
                    ),
            ])
            ->filters([
                SelectFilter::make('laboratory')
                    ->label(__('panel.booking.lab_space'))
                    ->relationship('laboratory', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('availability')
                    ->label(__('panel.booking.availability'))
                    ->options([
                        'available' => __('panel.booking.available'),
                        'occupied' => __('panel.booking.occupied'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'available') {
                            $query->having('booking_count', 0);
                        } elseif ($data['value'] === 'occupied') {
                            $query->having('booking_count', '>', 0);
                        }
                    }),
            ])
            ->filtersFormColumns(2)
            ->headerActions([
                TableAction::make('ver_calendario')
                    ->label(__('panel.booking.view_calendar'))
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->url('/admin/bookings/calendario'),
            ])
            ->actions([
                TableAction::make('reservar')
                    ->label(__('panel.booking.reserve'))
                    ->button()
                    ->disabled(
                        fn (Schedule $record): bool => $record->booking_count > 0
                    )
                    ->modalHeading(__('panel.booking.reserve_request_title'))
                    ->modalDescription(__('panel.booking.reserve_request_description'))
                    ->modalWidth('2xl')
                    ->form([
                        Section::make(__('panel.booking.selected_slot'))
                            ->icon('heroicon-o-calendar-days')
                            ->collapsed(false)
                            ->compact()
                            ->schema([
                                Hidden::make('laboratory_id')
                                    ->default(fn (Schedule $record) => $record->laboratory_id)
                                    ->required(),
                                Hidden::make('start_at')
                                    ->default(fn (Schedule $record) => $record->start_at),
                                Hidden::make('end_at')
                                    ->default(fn (Schedule $record) => $record->end_at),
                                Grid::make(3)->schema([
                                    Placeholder::make('laboratory_display')
                                        ->label(__('panel.booking.selected_lab'))
                                        ->content(fn (Schedule $record) => $record->laboratory->name ?? 'No asignado'),
                                    Placeholder::make('start_display')
                                        ->label(__('panel.booking.start'))
                                        ->content(fn (Schedule $record) => Carbon::parse($record->start_at)->locale(app()->getLocale())->translatedFormat('D d M Y - g:i A')),
                                    Placeholder::make('end_display')
                                        ->label(__('panel.booking.end'))
                                        ->content(fn (Schedule $record) => Carbon::parse($record->end_at)->locale(app()->getLocale())->translatedFormat('D d M Y - g:i A')),
                                ]),
                            ]),

                        Section::make(__('panel.booking.project_info'))
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Radio::make('project_type')
                                    ->label(__('panel.booking.project_type'))
                                    ->options([
                                        'Trabajo de grado' => __('panel.booking.project_type_degree'),
                                        'Investigación profesoral' => __('panel.booking.project_type_research'),
                                    ])
                                    ->inline()
                                    ->required(),
                                Grid::make(2)->schema([
                                    Select::make('academic_program')
                                        ->label(__('panel.booking.academic_program'))
                                        ->options(fn () => AcademicProgram::where('is_active', true)
                                            ->orderBy('name')
                                            ->pluck('name', 'name'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Select::make('semester')
                                        ->label(__('panel.booking.semester'))
                                        ->options(array_combine(range(1, 10), range(1, 10)))
                                        ->required(),
                                ]),
                                TextInput::make('research_name')
                                    ->label(__('panel.booking.research_name'))
                                    ->placeholder(__('panel.booking.research_name_placeholder'))
                                    ->required(),
                            ]),

                        Section::make(__('panel.booking.participants'))
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Select::make('applicants')
                                    ->label(__('panel.booking.applicants'))
                                    ->helperText(__('panel.booking.applicants_help'))
                                    ->multiple()
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search) => User::where('name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn ($user) => [$user->id => "{$user->name} {$user->last_name} - {$user->email}"]))
                                    ->required(),
                                Select::make('advisor')
                                    ->label(__('panel.booking.advisor'))
                                    ->helperText(__('panel.booking.advisor_help'))
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search) => User::where('name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn ($user) => [$user->id => "{$user->name} {$user->last_name} - {$user->email}"]))
                                    ->required(),
                            ]),

                        Section::make(__('panel.booking.materials'))
                            ->icon('heroicon-o-beaker')
                            ->schema([
                                Select::make('products')
                                    ->label(__('panel.booking.required_products'))
                                    ->helperText(__('panel.booking.required_products_help'))
                                    ->multiple()
                                    ->searchable()
                                    ->options(fn () => cache()->remember(
                                        'products-for-booking',
                                        config('cache.ttl.products', 600),
                                        fn () => Product::with('laboratory')->get()->mapWithKeys(fn ($p) => [$p->id => "{$p->name} — {$p->laboratory->name}"])->toArray()
                                    ))
                                    ->required(),
                            ]),
                    ])
                    ->action(function (Schedule $record, array $data): void {
                        $user = Auth::user();
                        $applicantNames = User::whereIn('id', $data['applicants'])->get()->map(fn ($user) => "{$user->name} {$user->last_name}")->implode(', ');
                        $advisorUser = User::find($data['advisor']);
                        $advisorName = $advisorUser ? "{$advisorUser->name} {$advisorUser->last_name}" : '';
                        Booking::create([
                            'schedule_id' => $record->id,
                            'user_id' => $user->id,
                            'project_type' => $data['project_type'],
                            'laboratory_id' => $data['laboratory_id'],
                            'academic_program' => $data['academic_program'],
                            'semester' => $data['semester'],
                            'applicants' => $applicantNames,
                            'research_name' => $data['research_name'],
                            'advisor' => $advisorName,
                            'products' => $data['products'],
                            'start_at' => $data['start_at'],
                            'end_at' => $data['end_at'],
                            'status' => Booking::STATUS_PENDING,
                        ]);
                    })
                    ->successRedirectUrl(url()->previous())
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('panel.booking.success_title'))
                            ->body(__('panel.booking.success_body'))
                            ->duration(5005)
                    ),
            ])
            ->defaultSort('start_at', 'asc')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->striped();
    }

    public static function getWidgets(): array
    {
        return [];
    }
}
