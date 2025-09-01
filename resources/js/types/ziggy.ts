import { Config, RouteParams } from 'ziggy-js';

interface Router {
    current(): string | undefined;
    current<T extends string>(name: T, params?: any): boolean;
    params: Record<string, string>;
    routeParams: Record<string, string>;
    queryParams: Record<string, any>;
    has<T extends string>(name: T): boolean;
}

declare global {
    function route(): Router;
    function route(name: string, params?: RouteParams<typeof name> | undefined, absolute?: boolean): string;
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof route;
    }
}
