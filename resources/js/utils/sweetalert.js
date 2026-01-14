import Swal from 'sweetalert2'

/**
 * SweetAlert2 utility wrapper for admin dashboard confirmations
 */

// Base confirmation dialog with customizable options
export const confirmAction = (options) => {
    return Swal.fire({
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#8b5cf6',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        ...options
    })
}

// Confirm deletion of an item
export const confirmDelete = (itemName = 'this item') => {
    return confirmAction({
        title: 'Are you sure?',
        text: `You are about to delete ${itemName}. This action cannot be undone.`,
        icon: 'warning',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: '#ef4444'
    })
}

// Confirm update/edit of an item
export const confirmUpdate = (itemName = 'this item') => {
    return confirmAction({
        title: 'Confirm Update',
        text: `You are about to update ${itemName}.`,
        icon: 'question',
        confirmButtonText: 'Yes, update it!'
    })
}

// Confirm banning a user
export const confirmBan = (userName) => {
    return confirmAction({
        title: 'Ban User?',
        text: `This will revoke all access tokens for ${userName}. They will not be able to login.`,
        icon: 'warning',
        confirmButtonText: 'Yes, ban user!',
        confirmButtonColor: '#ef4444'
    })
}

// Confirm unbanning a user
export const confirmUnban = (userName) => {
    return confirmAction({
        title: 'Unban User?',
        text: `This will restore access for ${userName}.`,
        icon: 'question',
        confirmButtonText: 'Yes, unban user!',
        confirmButtonColor: '#22c55e'
    })
}

// Confirm role change
export const confirmRoleChange = (userName, action) => {
    return confirmAction({
        title: 'Change User Role?',
        text: `You are about to ${action} for ${userName}.`,
        icon: 'question',
        confirmButtonText: 'Yes, proceed!'
    })
}

// Confirm course action (hide, reject, etc.)
export const confirmCourseAction = (courseName, action) => {
    return confirmAction({
        title: `${action} Course?`,
        text: `You are about to ${action.toLowerCase()} "${courseName}".`,
        icon: 'warning',
        confirmButtonText: `Yes, ${action.toLowerCase()}!`
    })
}

// Confirm enrollment action
export const confirmEnrollmentAction = (action, details) => {
    return confirmAction({
        title: `${action} Enrollment?`,
        text: details,
        icon: 'question',
        confirmButtonText: 'Yes, proceed!'
    })
}

// Show success message
export const showSuccess = (message, title = 'Success!') => {
    return Swal.fire({
        icon: 'success',
        title,
        text: message,
        timer: 2500,
        showConfirmButton: false
    })
}

// Show error message
export const showError = (message, title = 'Error') => {
    return Swal.fire({
        icon: 'error',
        title,
        text: message
    })
}

// Show info message
export const showInfo = (message, title = 'Info') => {
    return Swal.fire({
        icon: 'info',
        title,
        text: message
    })
}

// Prompt for input (e.g., rejection reason)
export const promptInput = (options) => {
    return Swal.fire({
        input: 'textarea',
        inputPlaceholder: 'Enter your reason...',
        showCancelButton: true,
        confirmButtonColor: '#8b5cf6',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason!'
            }
        },
        ...options
    })
}

export default Swal
