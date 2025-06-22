import Error from "@/Layouts/Error.jsx";

export default function Error403() {
    return (
        <Error
            title="Forbidden"
            message="Sorry, you are not authorized to access this page."
        />
    );
}
