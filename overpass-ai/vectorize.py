"""
Text vectorization using sentence-transformers.
Generates embeddings for semantic similarity search.
"""

from sentence_transformers import SentenceTransformer

# Load model once at module level for performance
model = SentenceTransformer('all-MiniLM-L6-v2')

def vectorize_text(text: str) -> list:
    """
    Convert text to a 384-dimensional embedding vector.

    Args:
        text: The text to vectorize

    Returns:
        List of floats representing the embedding
    """
    if not text:
        raise ValueError('Text cannot be empty')

    # Generate embedding
    embedding = model.encode([text])[0]

    # Convert to Python list for JSON serialization
    return embedding.tolist()
