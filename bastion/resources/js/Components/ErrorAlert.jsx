import { XCircleIcon } from '@heroicons/react/20/solid';

export default function ErrorAlert({ status }) {
    return (
        <div className="rounded-md bg-red-50 p-4">
            <div className="flex">
                <div className="flex-shrink-0">
                    <XCircleIcon aria-hidden="true" className="h-5 w-5 text-red-400"/>
                </div>
                <div className="ml-3">
                    { status }
                </div>
            </div>
        </div>
    );
}
