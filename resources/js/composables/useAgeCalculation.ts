import { differenceInYears, differenceInMonths, parseISO } from 'date-fns';

/**
 * Composable for age and date calculations
 * Centralizes date formatting logic used across components
 */
export function useAgeCalculation() {
  const calculateAge = (birthdate: string): string => {
    if (!birthdate) return 'Unknown age';
    
    const birthDate = parseISO(birthdate);
    const now = new Date();
    const years = differenceInYears(now, birthDate);
    
    if (years > 0) {
      return `${years} tahun`;
    }
    
    const months = differenceInMonths(now, birthDate);
    return `${months} bulan`;
  };

  return {
    calculateAge
  };
}