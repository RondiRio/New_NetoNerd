using UnityEngine;

public class VRColorChange : MonoBehaviour
{
    private Renderer cubeRenderer;
    private float gazeTime = 0f;
    private float gazeDuration = 2f; // Tempo necessário para ativar a mudança

    void Start()
    {
        cubeRenderer = GetComponent<Renderer>();
    }

    void Update()
    {
        Ray ray = new Ray(Camera.main.transform.position, Camera.main.transform.forward);
        RaycastHit hit;

        if (Physics.Raycast(ray, out hit))
        {
            if (hit.collider.gameObject == gameObject)
            {
                gazeTime += Time.deltaTime;
                if (gazeTime >= gazeDuration)
                {
                    ChangeColor();
                    gazeTime = 0f;
                }
            }
            else
            {
                gazeTime = 0f;
            }
        }
        else
        {
            gazeTime = 0f;
        }
    }

    void ChangeColor()
    {
        cubeRenderer.material.color = new Color(Random.value, Random.value, Random.value);
    }
}