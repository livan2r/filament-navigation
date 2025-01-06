<?php

namespace RyanChandler\FilamentNavigation;

use App\Models\Page;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
use Illuminate\Support\Str;
use RyanChandler\FilamentNavigation\Filament\Resources\NavigationResource;
use RyanChandler\FilamentNavigation\Models\Navigation;

class FilamentNavigation implements Plugin
{
    protected string $model = Navigation::class;

    protected string $resource = NavigationResource::class;

    protected array $itemTypes = [];

    protected array | Closure $extraFields = [];

    public function getId(): string
    {
        return 'navigation';
    }

    /** @param class-string<\Filament\Resources\Resource> $resource */
    public function usingResource(string $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    /** @param class-string<\Illuminate\Database\Eloquent\Model> $model */
    public function usingModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function itemType(string $name, array | Closure $fields, ?string $slug = null): static
    {
        $this->itemTypes[$slug ?? Str::slug($name)] = [
            'name' => $name,
            'fields' => $fields,
        ];

        return $this;
    }

    public function withExtraFields(array | Closure $schema): static
    {
        $this->extraFields = $schema;

        return $this;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([$this->getResource()]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }

    public static function get(): static
    {
        return filament('navigation');
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getExtraFields(): array | Closure
    {
        return $this->extraFields;
    }

    public function getItemTypes(): array
    {
        return array_merge(
            [
                'external-link' => [
                    'name' => __('filament-navigation::filament-navigation.attributes.external-link'),
                    'fields' => [
                        TextInput::make('url')
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->prefixIconColor('secondary')
                            ->label(__('filament-navigation::filament-navigation.attributes.url'))
                            ->helperText(__('filament-navigation::filament-navigation.attributes.url-helper'))
                            ->activeUrl()
                            ->required(),
                        Select::make('target')
                            ->prefixIcon('heroicon-o-cursor-arrow-rays')
                            ->prefixIconColor('secondary')
                            ->label(__('filament-navigation::filament-navigation.attributes.target'))
                            ->helperText(__('filament-navigation::filament-navigation.attributes.target-helper'))
                            ->options([
                                '' => __('filament-navigation::filament-navigation.select-options.same-tab'),
                                '_blank' => __('filament-navigation::filament-navigation.select-options.new-tab'),
                            ])
                            ->default('')
                            ->selectablePlaceholder(false),
                    ],
                ],
            ],
            // FIXME: Translations doesn't work for these options
            [
                'page' => [
                    'name' => __('admin.page.singular'),
                    'fields' => [
                        Select::make('page_id')
                            ->label(__('admin.navigation.page.label'))
                            ->helperText(__('admin.navigation.page.desc'))
                            ->prefixIcon('heroicon-o-cursor-arrow-rays')
                            ->prefixIconColor('secondary')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->options(Page::where('enabled', true)->pluck('name', 'id')),
                        Select::make('page_target')
                            ->label(__('admin.navigation.target.label'))
                            ->helperText(__('admin.navigation.target.desc'))
                            ->prefixIcon('heroicon-o-cursor-arrow-rays')
                            ->prefixIconColor('secondary')
                            ->options([
                                '' => __('admin.navigation.target.same-tab'),
                                '_blank' => __('admin.navigation.target.new-tab'),
                            ])
                            ->default('')
                            ->selectablePlaceholder(false)
                    ],
                ]
            ],
            [
                'mega-menu' => [
                    'name' => __('admin.navigation.mega_menu.label'),
                    'fields' => [
                        Select::make('mega_menu')
                            ->label(__('admin.navigation.mega_menu.label'))
                            ->prefixIcon('heroicon-o-cursor-arrow-rays')
                            ->prefixIconColor('secondary')
                            ->helperText(__('admin.navigation.mega_menu.desc'))
                            ->default('three_columns')
                            ->preload()
                            ->native(false)
                            ->options(__('admin.navigation.mega_menu.options')),
                        CuratorPicker::make('image')
                            ->label(__('admin.navigation.image.label'))
                            ->helperText(__('admin.navigation.image.desc'))
                            ->color('primary')
                            ->outlined(false)
                            ->constrained(),
                    ],
                ],
            ],
            $this->itemTypes
        );
    }
}
