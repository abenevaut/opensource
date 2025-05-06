import { useRef, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from "@abenevaut/tailwindui/src/js/CatalystInertia/Button";
import { ErrorMessage, Field, FieldGroup, Label } from "@abenevaut/tailwindui/src/js/CatalystInertia/fieldset.jsx";
import SuccessAlert from "@/Components/SuccessAlert.jsx";
import { Input } from "@abenevaut/tailwindui/src/js/CatalystInertia/input.jsx";
import { Fieldset } from "@abenevaut/tailwindui/src/js/CatalystInertia/fieldset";
import { Alert, AlertActions, AlertDescription, AlertTitle } from '@abenevaut/tailwindui/src/js/CatalystInertia/alert'

export default function DeleteUserForm({ className = '' }) {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef();

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
    } = useForm({
        password: '',
    });

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    const deleteUser = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);

        reset();
    };

    return (
        <section className={`space-y-6 ${className}`}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Delete Account</h2>

                <p className="mt-1 text-sm text-gray-600">
                    Once your account is deleted, all of its resources and data will be permanently deleted. Before
                    deleting your account, please download any data or information that you wish to retain.
                </p>
            </header>

            <Fieldset>

                <FieldGroup>
                    <div><Button onClick={confirmUserDeletion}>Delete Account</Button></div>
                </FieldGroup>

            </Fieldset>

            <Alert open={confirmingUserDeletion} onClose={closeModal}>
                <AlertTitle>Are you sure you want to delete your account?</AlertTitle>
                <AlertDescription>
                    Once your account is deleted, all of its resources and data will be permanently deleted. Please
                    enter your password to confirm you would like to permanently delete your account.
                </AlertDescription>
                <AlertActions>

                    <form onSubmit={ deleteUser } className="p-6">


                        <Fieldset>


                            <FieldGroup>

                                {
                                    !!status
                                    && <SuccessAlert status={ status }/>
                                }

                            </FieldGroup>

                            <FieldGroup>

                                <Field>
                                    <Label>Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        autoComplete="password"
                                        invalid={ !!errors.password }
                                        value={ data.password }
                                        onChange={ (e) => setData('password', e.target.value) }
                                    />
                                    {
                                        !!errors.password
                                        && <ErrorMessage>{ errors.password }</ErrorMessage>
                                    }
                                </Field>


                                <div>
                                    <Button onClick={ () => setIsOpen(false) }>Cancel</Button>

                                    <Button className="ms-3" disabled={ processing } onClick={ () => setIsOpen(false) }>
                                        Delete Account
                                    </Button>
                                </div>


                            </FieldGroup>

                        </Fieldset>

                    </form>

                </AlertActions>
            </Alert>

        </section>
    );
}
