# 🔍 Quantum Infrastructure Zero - API Reference

## Table of Contents
- [DIDN (Distributed Identity & Data Network)](#didn)
- [QMP (Quantum Mesh Protocol)](#qmp)
- [AI Nodes](#ai-nodes)
- [Self-Contained CI/CD](#self-contained-cicd)

---

## DIDN (Distributed Identity & Data Network)

### `class DIDN`
Core class for managing decentralized identities and data.

#### Methods

##### `register_identity(public_key: str, signature: str, metadata: Dict = None) -> str`
Register a new identity in the network.

**Parameters:**
- `public_key`: The public key of the identity
- `signature`: Cryptographic signature of the public key
- `metadata`: Optional metadata dictionary

**Returns:**
- `str`: Unique identity ID

**Example:**
```python
identity_id = didn.register_identity(
    public_key="user_pub_key",
    signature="sig_123",
    metadata={"name": "Alice"}
)
```

##### `store_data(identity_id: str, data: Dict, signature: str) -> str`
Store data in the network.

**Parameters:**
- `identity_id`: ID of the identity storing the data
- `data`: Dictionary of data to store
- `signature`: Signature of the data

**Returns:**
- `str`: Data ID for retrieval

##### `resolve_identity(identity_id: str) -> Optional[Identity]`
Retrieve an identity by ID.

**Parameters:**
- `identity_id`: ID of the identity to retrieve

**Returns:**
- `Optional[Identity]`: Identity object or None if not found

## QMP (Quantum Mesh Protocol)

### `class QMPService`
Implements the Quantum Mesh Protocol for decentralized communication.

#### Methods

##### `async start(host: str = '0.0.0.0', port: int = 0) -> Tuple[str, int]`
Start the QMP service.

**Parameters:**
- `host`: Host to bind to
- `port`: Port to listen on (0 for auto-select)

**Returns:**
- `Tuple[str, int]`: (host, port) the service is running on

##### `register_handler(message_type: str, handler: Callable)`
Register a message handler.

**Parameters:**
- `message_type`: Type of message to handle
- `handler`: Async function that takes (message, writer)

##### `async broadcast(message: QMPMessage, exclude: set = None)`
Broadcast a message to all connected nodes.

**Parameters:**
- `message`: Message to broadcast
- `exclude`: Set of writers to exclude

## AI Nodes

### `class AINode`
Implements federated learning capabilities.

#### Methods

##### `async train(data: Dict[str, Any], epochs: int = 1) -> ModelUpdate`
Train the model on local data.

**Parameters:**
- `data`: Training data
- `epochs`: Number of training epochs

**Returns:**
- `ModelUpdate`: Training results

##### `async aggregate_updates() -> Dict[str, Any]`
Aggregate updates from all nodes.

**Returns:**
- `Dict[str, Any]`: Aggregated model weights

## Self-Contained CI/CD

### `class SelfContainedCICD`
Implements autonomous build, test, and deployment.

#### Methods

##### `async run_pipeline() -> bool`
Run the complete CI/CD pipeline.

**Returns:**
- `bool`: True if all steps succeeded

##### `create_artifact(source_path: str, name: str, metadata: Dict = None) -> BuildArtifact`
Create a build artifact.

**Parameters:**
- `source_path`: Path to source file
- `name`: Name for the artifact
- `metadata`: Optional metadata

**Returns:**
- `BuildArtifact`: Created artifact
