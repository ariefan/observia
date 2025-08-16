/**
 * Composable for common navigation functions
 * Centralizes navigation logic used across components
 */
export function useNavigation() {
  const back = () => window.history.back();

  return {
    back
  };
}