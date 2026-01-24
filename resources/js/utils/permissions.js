export const can = (permission) => {
    const permissions = JSON.parse(localStorage.getItem('permissions') || '[]');
    return permissions.includes(permission);
};

export const canAny = (permissionsList) => {
    const permissions = JSON.parse(localStorage.getItem('permissions') || '[]');
    return permissionsList.some(p => permissions.includes(p));
};
