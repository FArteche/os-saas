import { Link, router, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, Plus } from 'lucide-react';
import CurrentCompanyController from '@/actions/App/Http/Controllers/CurrentCompanyController';
import AppLogo from '@/components/app-logo';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useInitials } from '@/hooks/use-initials';
import { useIsMobile } from '@/hooks/use-mobile';
import { dashboard } from '@/routes';
import { create as createCompany } from '@/routes/onboarding';

export function CompanySwitcher() {
    const { currentCompany, companies } = usePage().props;
    const { state } = useSidebar();
    const isMobile = useIsMobile();
    const getInitials = useInitials();

    // Pages outside a company (e.g. profile settings before onboarding) keep the plain logo.
    if (!currentCompany) {
        return (
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" asChild>
                        <Link href={dashboard()} prefetch>
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        );
    }

    const switchTo = (companyId: number) => {
        if (companyId === currentCompany.id) {
            return;
        }

        router.put(CurrentCompanyController.update.url(), {
            company_id: companyId,
        });
    };

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="lg"
                            className="data-[state=open]:bg-sidebar-accent"
                            data-test="company-switcher"
                        >
                            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-xs font-semibold text-primary-foreground">
                                {getInitials(currentCompany.name)}
                            </div>
                            <div className="grid flex-1 text-left text-sm leading-tight">
                                <span className="truncate font-semibold">
                                    {currentCompany.name}
                                </span>
                                {currentCompany.roleLabel && (
                                    <span className="truncate text-xs text-muted-foreground">
                                        {currentCompany.roleLabel}
                                    </span>
                                )}
                            </div>
                            <ChevronsUpDown className="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>

                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={
                            isMobile || state !== 'collapsed'
                                ? 'bottom'
                                : 'right'
                        }
                    >
                        <DropdownMenuLabel className="text-xs text-muted-foreground">
                            Empresas
                        </DropdownMenuLabel>

                        {companies.map((company) => (
                            <DropdownMenuItem
                                key={company.id}
                                onSelect={() => switchTo(company.id)}
                                className="gap-2"
                            >
                                <div className="flex size-6 items-center justify-center rounded-sm border text-[10px] font-semibold">
                                    {getInitials(company.name)}
                                </div>
                                <span className="truncate">{company.name}</span>
                                {company.id === currentCompany.id && (
                                    <Check className="ml-auto size-4" />
                                )}
                            </DropdownMenuItem>
                        ))}

                        <DropdownMenuSeparator />

                        <DropdownMenuItem asChild>
                            <Link href={createCompany()} className="gap-2">
                                <div className="flex size-6 items-center justify-center rounded-sm border">
                                    <Plus className="size-4" />
                                </div>
                                Nova empresa
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
