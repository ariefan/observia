import { cva, type VariantProps } from 'class-variance-authority'

export { default as Alert } from './Alert.vue'
export { default as AlertDescription } from './AlertDescription.vue'
export { default as AlertTitle } from './AlertTitle.vue'

export const alertVariants = cva(
  'relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground',
  {
    variants: {
      variant: {
        default: 'bg-background text-foreground',
        destructive:
          'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
        success:
          'bg-green-100 border border-green-500/50 text-green-700 dark:bg-green-900/20 dark:text-green-400 [&>svg]:text-green-500',
        warning:
          'bg-yellow-100 border border-yellow-500/50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 [&>svg]:text-yellow-500',
        danger:
          'bg-red-100 border border-red-500/50 text-red-700 dark:bg-red-900/20 dark:text-red-400 [&>svg]:text-red-500',
        info:
          'bg-blue-100 border border-blue-500/50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 [&>svg]:text-blue-500',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
)

export type AlertVariants = VariantProps<typeof alertVariants>
