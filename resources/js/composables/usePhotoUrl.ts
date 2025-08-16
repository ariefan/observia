/**
 * Composable for handling photo URL formatting
 * Centralizes logic for Firebase vs local storage photo handling
 */
export function usePhotoUrl() {
  const getPhotoUrl = (photoPath: string | null | undefined): string => {
    if (!photoPath) return '';
    
    // Handle Firebase storage URLs
    if (photoPath.includes('firebasestorage.googleapis.com')) {
      return photoPath;
    }
    
    // Handle legacy public/ paths
    if (photoPath.startsWith('public/')) {
      return `/storage/${photoPath.substring(7)}`;
    }
    
    // Handle regular storage paths
    return `/storage/${photoPath}`;
  };

  return {
    getPhotoUrl
  };
}