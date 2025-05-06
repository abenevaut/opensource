import Error from "@/Layouts/Error.jsx";

export default function Error401() {
    return (
        <Error
            title="Unauthorized"
            message="Sorry, you are not authorized to access this page."
        />
    );
}
