import { CheckCircleIcon } from '@heroicons/react/20/solid';

export default function SuccessAlert({ status }) {
    return (
        <div className="rounded-md bg-green-50 p-4">
            <div className="flex">
                <div className="flex-shrink-0">
                    <CheckCircleIcon aria-hidden="true" className="h-5 w-5 text-green-400"/>
                </div>
                <div className="ml-3">
                    { status }
                </div>
            </div>
        </div>
    );
}
