export type UserTableRow = {
    id: number;
    name: string;
    email: string;
    status: string;
    status_value: string;
};

export type UserStatusOption = {
    value: string;
    label: string;
};

export type UserForm = {
    name: string;
    email: string;
    password?: string;
    password_confirmation?: string;
    status?: string;
}

export type EditableUser = {
    id: number;
    name: string;
    email: string;
    status: string;
};
